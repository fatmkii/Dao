<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Forum;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\QueryException;
use App\Common\ResponseCode;
use Carbon\Carbon;
use App\Exceptions\CoinException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Jobs\ProcessUserActive;
use App\Models\GambleQuestion;
use App\Models\VoteQuestion;

class ThreadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'forum_id' => 'required|integer',
            'title' => 'required|string|max:100',
            'content' => 'required|string|max:20000',
            'anti_jingfen' => 'required|boolean',
            'nissin_time' => 'integer',
            'random_heads_group' => 'integer',
            'post_with_admin' => 'boolean',
            'locked_by_coin' => 'integer|max:1000000|min:1',
            'is_vote' => 'boolean|required',
            'is_gamble' => 'boolean|required',
            'is_delay' => 'boolean|required',
            'can_battle' => 'boolean|required',
        ]);


        $user = $request->user;
        $water_check = $user->waterCheck('new_thread', $request->ip());
        if ($water_check != 'ok') return $water_check;


        //确认是否冒认管理员发公告或者管理员帖
        if (
            ($request->subtitle == "[公告]" || $request->post_with_admin == true) &&
            !in_array($request->forum_id, json_decode($user->AdminPermissions->forums))
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }

        //执行追加新主题流程
        try {
            DB::beginTransaction();
            //发主题帖（Thread）
            $thread = new Thread;
            if ($request->title_color) {
                $user->coin -= 500; //设置标题颜色减500奥利奥   
                $thread->title_color = $request->title_color;
            }
            if ($request->locked_by_coin > 0) {
                $user->coin -= 500; //设置奥利奥权限贴减500奥利奥  
                $thread->locked_by_coin = $request->locked_by_coin;
            }
            $thread->created_binggan = $request->binggan;
            $thread->forum_id = $request->forum_id;
            if ($request->subtitle == '[公告]') {
                $thread->sub_id = $request->admin_subtitle ?   10 : 99; //$request->admin_subtitle == 0是全岛公告。把sub_id=99
            }
            $thread->nickname = $request->nickname;
            $thread->created_ip = $request->ip();
            $thread->sub_title = $request->subtitle;
            $thread->random_heads_group = $request->random_heads_group;
            if ($request->nissin_time > 0) { //如果请求中带有nissin_time，才设定nissin_date
                if ($request->is_delay) { //如果是延迟发表主题，则从发表时刻计算日清时间
                    $hour_now = Carbon::now()->hour;
                    if ($hour_now < 8) { //根据时间确定8点日清的节点
                        $thread->nissin_date  = Carbon::today()->addHours(8)->addDays($request->nissin_time);
                    } else {
                        $thread->nissin_date  = Carbon::tomorrow()->addHours(8)->addDays($request->nissin_time);
                    }
                } else { //如果是非延迟发表主题，则直接计算日清时间
                    $thread->nissin_date = Carbon::now()->addDays($request->nissin_time);
                }
            }
            $thread->title = $request->title;
            $thread->anti_jingfen = $request->anti_jingfen;
            $thread->can_battle = $request->can_battle;
            $thread->is_delay = $request->is_delay;
            $thread->save();
            //发主题帖的第0楼（Post）
            $post = new Post;
            $post->setSuffix(intval($thread->id / 10000));
            $post->created_binggan = $request->binggan;
            $post->forum_id = $request->forum_id;
            $post->thread_id = $thread->id;
            $post->content = $request->content;
            $post->nickname = $request->nickname;
            $post->created_by_admin = $request->post_with_admin  ? 1 : 0;
            $post->created_ip = $request->ip();
            $post->random_head = random_int(0,39);
            $post->floor = 0;
            $post->save();

            //追加投票贴
            if ($request->is_vote) {
                $user->coin -= 1000; //发投票主题减1000奥利奥  
                $vote_question = new VoteQuestion();
                $thread->vote_question_id = $vote_question->create($request, $thread->id); //$vote_question->create会返回id
                $thread->save();
            }

            //追加菠菜贴
            if ($request->is_gamble) {
                $user->coin -= 500; //发菠菜主题减500奥利奥  
                $gamble_question = new GambleQuestion();
                $thread->gamble_question_id = $gamble_question->create($request, $thread->id); //$gamble_question->create会返回id
                $thread->save();
            }

            //统一判断奥利奥是否足够
            if ($user->coin < 0) {
                throw new CoinException();
            }
            $user->save();

            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试',
            ]);
        } catch (CoinException $e) {
            DB::rollback();
            return response()->json(
                [
                    'code' => ResponseCode::COIN_NOT_ENOUGH,
                    'message' => ResponseCode::$codeMap[ResponseCode::COIN_NOT_ENOUGH],
                ],
            );
        }

        $user->waterRecord('new_thread', $request->ip()); //用redis记录发帖频率。

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户发表了新主题',
                'thread_id' => $thread->id,
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '发表主题成功！',
                'data' => [
                    'forum_id' => $request->forum_id,
                    'thread_id' => $request->thread_id,
                ]
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $Thread_id)
    {
        $request->validate([
            'binggan' => 'string|nullable',
            'page' => 'integer|nullable',
            'search_content' => 'string|max:100', //搜索内容
        ]);

        $CurrentThread = Thread::find($Thread_id);
        if (!$CurrentThread || $CurrentThread->is_delay == 1) {
            return response()->json([
                'code' => ResponseCode::THREAD_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::THREAD_NOT_FOUND],
            ]);
        }

        $CurrentForum = $CurrentThread->forum;
        $user = $request->user;

        //用redis记录，全局每10秒搜索20次限制
        if ($request->has('search_content')) {
            if (Redis::exists('search_record_global')) {
                Redis::incr('search_record_global');
                if (Redis::GET('search_record_global') > 20) {
                    return response()->json([
                        'code' => ResponseCode::SEARCH_TOO_MANY,
                        'message' => ResponseCode::$codeMap[ResponseCode::SEARCH_TOO_MANY],
                    ]);
                }
            } else {
                Redis::setex('search_record_global',  10, 1);
            }
        }


        //判断是否可无饼干访问的板块
        if (!$CurrentForum->is_anonymous && !$user) {
            return response()->json([
                'code' => ResponseCode::USER_NOT_FOUND,
                'message' => '本小岛需要饼干才能查看喔',
            ]);
        }

        //判断是否达到可以访问板块的最少奥利奥
        if ($CurrentForum->accessible_coin > 0) {
            if (!$user) {
                return response()->json([
                    'code' => ResponseCode::USER_NOT_FOUND,
                    'message' => '本小岛需要饼干才能查看喔',
                ]);
            }
            if ($user->coin < $CurrentForum->accessible_coin && $user->admin == 0) {
                return response()->json([
                    'code' => ResponseCode::THREAD_UNAUTHORIZED,
                    'message' => sprintf("本小岛需要拥有大于%u奥利奥才能查看喔", $CurrentForum->accessible_coin),
                ]);
            }
        }

        //判断奥利奥锁定权限贴
        if ($CurrentThread->locked_by_coin > 0) {
            if (!$user) {
                return response()->json([
                    'code' => ResponseCode::USER_NOT_FOUND,
                    'message' => '本贴需要饼干才能查看喔',
                ]);
            }
            if ($user->coin < $CurrentThread->locked_by_coin && $user->admin == 0) {
                return response()->json([
                    'code' => ResponseCode::THREAD_UNAUTHORIZED,
                    'message' => sprintf("本贴需要拥有大于%u奥利奥才能查看喔", $CurrentThread->locked_by_coin),
                ]);
            }
        }

        //各种日清模式
        switch ($CurrentForum->is_nissin) {
            case 0:
                break;
            case 1: //按照8点日清模式
                $hour_now = Carbon::now()->hour;
                if ($hour_now >= 8) { //根据时间确定8点日清的节点
                    $nissin_breakpoint = Carbon::today()->addHours(8);
                } else {
                    $nissin_breakpoint = Carbon::yesterday()->addHours(8);
                }
                if (
                    $CurrentThread->created_at < $nissin_breakpoint
                    && $CurrentThread->sub_id == 0
                ) {
                    if ($user != null && $user->admin == 99) {
                        break;
                    } else {
                        return response()->json([
                            'code' => ResponseCode::THREAD_WAS_NISSINED,
                            'message' => ResponseCode::$codeMap[ResponseCode::THREAD_WAS_NISSINED],
                        ]);
                    }
                }
                break;
            case 2: //按照可选日清时间模式
                if (
                    $CurrentForum->is_nissin
                    && $CurrentThread->nissin_date < Carbon::now()
                    && $CurrentThread->sub_id == 0
                ) {
                    if ($user != null && $user->admin == 99) {
                        break;
                    } else {
                        return response()->json([
                            'code' => ResponseCode::THREAD_WAS_NISSINED,
                            'message' => ResponseCode::$codeMap[ResponseCode::THREAD_WAS_NISSINED],
                        ]);
                    }
                }
                break;
        }


        if ($request->has('search_content')) { //搜索内容
            $posts = $CurrentThread->posts()->where('content', 'like', '%' . $request->search_content . '%')->orderBy('id', 'asc')->paginate(200);
        } else {
            $posts = $CurrentThread->posts()->orderBy('id', 'asc')->paginate(200);
        }

        if ($posts->currentPage() > 1) {
            $posts->appendPost0($CurrentThread->posts()->first()); //为第2页及之后增加0楼
        }

        //如果有提供binggan，为每个post输入binggan，用来判断is_your_post（为前端提供是否是用户自己帖子的判据）
        if ($request->query('binggan')) {
            foreach ($posts as $post) {
                $post->setBinggan($request->query('binggan'));
            }
        }

        //为反精分帖子加上created_binggan_hash
        if ($CurrentThread->anti_jingfen) {
            $posts->append('created_binggan_hash');
        }

        //为超管加入发帖者饼干显示
        if ($user && $user->admin == 99) {
            $posts->makeVisible('created_binggan');
        }

        //提供该帖子的随机头像地址表
        // $random_heads = Cache::remember('random_heads_cache_' . $CurrentThread->random_heads_group, 7 * 24 * 3600, function () use ($CurrentThread) {
        //     return DB::table('random_heads')->find($CurrentThread->random_heads_group);
        // });

        //记录搜索行为
        if ($request->has('search_content')) {
            ProcessUserActive::dispatch(
                [
                    'binggan' => $user->binggan,
                    'user_id' => $user->id,
                    'active' => '用户进行了搜索（帖子）',
                    'content' => '关键词：' . $request->query('search_content'),
                    'thread_id' => $Thread_id,
                ]
            );
        }


        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'forum_data' => $CurrentForum,
            'thread_data' => $CurrentThread,
            'posts_data' => $posts,
            // 'random_heads' => $random_heads,
        ]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    //撤回延时发送的主题
    public function delay_thread_withdraw(Request $request, $Thread_id)
    {
        $CurrentThread = Thread::find($Thread_id);
        if (!$CurrentThread) {
            return response()->json([
                'code' => ResponseCode::THREAD_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::THREAD_NOT_FOUND],
            ]);
        }
        //确认是否确实是延迟主题
        if ($CurrentThread->is_delay != 1) {
            return response()->json([
                'code' => ResponseCode::USER_CANNOT,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_CANNOT],
            ]);
        }

        $user = $request->user;
        //只有主题发起者才能撤回主题
        if ($CurrentThread->created_binggan != $user->binggan) {
            return response()->json([
                'code' => ResponseCode::USER_CANNOT,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_CANNOT],
                'user' => $user,
            ]);
        }

        try {
            DB::beginTransaction();
            $CurrentThread->is_deleted = 1;
            $CurrentThread->save();
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试',
            ]);
        }

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'thread_id' => $Thread_id,
        ]);
    }
}
