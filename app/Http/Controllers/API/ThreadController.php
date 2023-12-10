<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\Thread;
use App\Common\ResponseCode;
use Carbon\Carbon;
use App\Exceptions\CoinException;
use App\Facades\GlobalSetting;
use Illuminate\Support\Facades\Redis;
use App\Jobs\ProcessUserActive;
use App\Models\Crowd;
use App\Models\GambleQuestion;
use App\Models\Hongbao;
use App\Models\UserMedalRecord;
use App\Models\VoteQuestion;
use Exception;

class ThreadController extends Controller
{
    private function postsData($thread_id, $page, $searchContent = null)
    {
        $limit = 200; //每页200;
        $offset = $page * 200 - 200;
        $posts_table = 'posts_' . intval($thread_id / 10000);

        //原生SQL
        // $sql_posts = 'select * from ' . $posts_table . ' where thread_id = :thread_id limit 200 offset :offset';
        // $posts = DB::select($sql_posts, ['thread_id' => $thread_id, 'offset' => $offset]);

        //原生SQL（子语句）
        // $sql_posts = 'select * from ' . $posts_table .
        //     ' join(select id from ' . $posts_table . ' where thread_id = :thread_id limit 200 offset :offset) as temp using(id)';
        // $posts = DB::select($sql_posts, ['thread_id' => $thread_id, 'offset' => $offset]);

        //用子查询join
        if ($searchContent == null) {
            $sql_child = DB::table($posts_table)
                ->select('id')
                ->where('thread_id', $thread_id)
                ->offset($offset)->limit($limit);
        } else {
            $sql_child = DB::table($posts_table)
                ->select('id')
                ->where('thread_id', $thread_id)
                ->where('content', 'like', '%' . $searchContent . '%')
                ->where('is_deleted', 0)
                ->offset($offset)->limit($limit);
        }

        $posts = Post::suffix((intval($thread_id / 10000)))
            ->joinSub($sql_child, 'sql_child', function ($join) use ($posts_table) {
                $join->on($posts_table . '.id', '=', 'sql_child.id');
            })
            ->get();

        if ($page > 1) {
            $posts0 = Post::suffix((intval($thread_id / 10000)))->where('thread_id', $thread_id)->first(); //为第2页及之后增加0楼
            $posts->prepend($posts0); //为第2页及之后增加0楼
        }

        //查询最大页数
        if ($searchContent == null) {
            $sql_posts_num = 'select count(*) as count from ' . $posts_table . ' where thread_id = :thread_id';
            $lastPage = ceil(DB::select($sql_posts_num, ['thread_id' => $thread_id])[0]->count / 200);

            //查询最大页数。这个可能效率更好些? 但是先不用
            // $lastPage = ceil($CurrentThread->posts_num / 200);
        } else {
            $posts_num = DB::table($posts_table)
                ->where('thread_id', $thread_id)
                ->where('content', 'like', '%' . $searchContent . '%')
                ->count();
            $lastPage = ceil($posts_num / 200);
        }

        return array($posts, $lastPage);
    }


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
            'content' => 'required|string|max:100000',
            'anti_jingfen' => 'required|boolean',
            'nissin_time' => 'integer',
            'random_heads_group' => 'integer',
            'post_with_admin' => 'boolean',
            'locked_by_coin' => 'integer|max:25000|min:1',
            'thread_type' => 'required|string',
            'is_delay' => 'boolean|required',
            'is_private' => 'boolean|required',
            'can_battle' => 'boolean|required',
            'nickname' => 'max:30',
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

        //只有管理员可以发众筹
        if (
            $request->thread_type == "crowd"  &&
            !(in_array($user->admin, [99, 20, 10]) && in_array($request->forum_id, json_decode($user->AdminPermissions->forums)))
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
            if ($request->title_color != "" && $request->title_color != "#212529") {
                // $user->coin -= 500; //设置标题颜色减500奥利奥 
                // #212529是默认颜色，不收费
                $user->coinChange(
                    'normal', //记录类型
                    [
                        'olo' => -500,
                        'content' => '发布主题设置标题颜色',
                    ]
                ); //通过统一接口、记录操作  
                $thread->title_color = $request->title_color;
            }
            if ($request->locked_by_coin > 0) {
                // $user->coin -= 500; //设置奥利奥权限贴减500奥利奥  
                $user->coinChange(
                    'normal', //记录类型
                    [
                        'olo' => -500,
                        'content' => '发布olo权限主题',
                    ]
                ); //通过统一接口、记录操作
                $thread->locked_by_coin = $request->locked_by_coin;
            }
            if ($request->is_private == true) {
                $user->coinChange(
                    'normal', //记录类型
                    [
                        'olo' => -500,
                        'content' => '发布私密主题',
                    ]
                ); //通过统一接口、记录操作
                $thread->is_private = $request->is_private;
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
            // $post = new Post;
            // $post->setSuffix(intval($thread->id / 10000));
            // $post->created_binggan = $request->binggan;
            // $post->forum_id = $request->forum_id;
            // $post->thread_id = $thread->id;
            // $post->content = $request->content;
            // $post->nickname = $request->nickname;
            // $post->created_by_admin = $request->post_with_admin  ? 1 : 0;
            // $post->created_ip = $request->ip();
            // $post->random_head = random_int(0, 39);
            // $post->floor = 0;
            // $post->save();

            $post = Post::create([
                'created_binggan' => $request->binggan,
                'forum_id' => $request->forum_id,
                'thread_id' => $thread->id,
                'content' => $request->content,
                'nickname' => $request->nickname,
                'created_by_admin' => $request->post_with_admin  ? 1 : 0,
                'created_IP' => $request->ip(),
                'floor' => 0,
            ]);

            //追加投票贴
            if ($request->thread_type == "vote") {
                // $user->coin -= 1000; //发投票主题减1000奥利奥  
                $user->coinChange(
                    'normal', //记录类型
                    [
                        'olo' => -1000,
                        'content' => '发布投票主题',
                        'thread_id' => $thread->id,
                        'thread_title' => $thread->title,
                    ]
                ); //通过统一接口、记录操作  
                $vote_question = VoteQuestion::create($request, $thread->id);
                $thread->vote_question_id = $vote_question->id;
                $thread->save();
            }

            //追加菠菜贴
            if ($request->thread_type == "gamble") {
                // $user->coin -= 500; //发菠菜主题减500奥利奥  
                $user->coinChange(
                    'normal', //记录类型
                    [
                        'olo' => -500,
                        'content' => '发布菠菜主题',
                        'thread_id' => $thread->id,
                        'thread_title' => $thread->title,
                    ]
                ); //通过统一接口、记录操作  
                $gamble_question = GambleQuestion::create($request, $thread->id);
                $thread->gamble_question_id = $gamble_question->id; //$gamble_question->create会返回id
                $thread->save();
            }

            //追加菠众筹贴
            if ($request->thread_type == "crowd") {
                $crowd = Crowd::create($request, $thread->id);
                $thread->crowd_id = $crowd->id; //$crowd->create会返回id
                $thread->save();
            }

            //追加红包贴
            if ($request->thread_type == "hongbao") {
                $hongbao = Hongbao::create($request, $thread->id);
                $thread->hongbao_id = $hongbao->id; //$hongbao->create会返回id
                $thread->save();
            }


            //统一判断奥利奥是否足够
            if ($user->coin < 0) {
                throw new CoinException();
            }
            $user->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
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

        // if ($CurrentThread->is_deleted != 0 && $user->admin != 99) {
        //     //已删除的帖子不显示
        //     return response()->json([
        //         'code' => ResponseCode::THREAD_NOT_FOUND,
        //         'message' => ResponseCode::$codeMap[ResponseCode::THREAD_NOT_FOUND],
        //     ]);
        // }

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
            if ($user->getCoin() < $CurrentForum->accessible_coin && !(in_array($user->admin, [99, 20, 10]))) {
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
            if ($user->getCoin() < $CurrentThread->locked_by_coin && !(in_array($user->admin, [99, 20, 10]))) {
                return response()->json([
                    'code' => ResponseCode::THREAD_UNAUTHORIZED,
                    'message' => sprintf("本贴需要拥有大于%u奥利奥才能查看喔", $CurrentThread->locked_by_coin),
                ]);
            }
        }

        //判断是否私密主题 
        if ($CurrentThread->is_private == true) {
            if (!$user) {
                return response()->json([
                    'code' => ResponseCode::USER_NOT_FOUND,
                    'message' => '本贴需要饼干才能查看喔',
                ]);
            }
            if ($user->binggan != $CurrentThread->created_binggan && !(in_array($user->admin, [99, 20, 10]))) {
                return response()->json([
                    'code' => ResponseCode::THREAD_IS_PRIVATE,
                    'message' => '本贴是私密主题，只有发帖者可以查看喔',
                ]);
            }
        }

        //各种日清模式
        switch ($CurrentForum->is_nissin) {
            case 0:
                break;
            case 1: //按照8点日清模式
                //新日清判断模式
                if ($CurrentThread->has_nissined == True) {
                    if ($user != null && $user->admin == 99) {
                        break;
                    } else {
                        return response()->json([
                            'code' => ResponseCode::THREAD_WAS_NISSINED,
                            'message' => ResponseCode::$codeMap[ResponseCode::THREAD_WAS_NISSINED],
                        ]);
                    }
                }
                // $hour_now = Carbon::now()->hour;
                // if ($hour_now >= 8) { //根据时间确定8点日清的节点
                //     $nissin_breakpoint = Carbon::today()->addHours(8);
                // } else {
                //     $nissin_breakpoint = Carbon::yesterday()->addHours(8);
                // }
                // if (
                //     $CurrentThread->created_at < $nissin_breakpoint
                //     && $CurrentThread->sub_id == 0
                // ) {
                //     if ($user != null && $user->admin == 99) {
                //         break;
                //     } else {
                //         return response()->json([
                //             'code' => ResponseCode::THREAD_WAS_NISSINED,
                //             'message' => ResponseCode::$codeMap[ResponseCode::THREAD_WAS_NISSINED],
                //         ]);
                //     }
                // }
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

        $currentPage = $request->has("page") ? $request->page : 1;

        if ($request->has('search_content')) { //搜索内容
            // $posts = $CurrentThread->posts()->where('content', 'like', '%' . $request->search_content . '%')->orderBy('id', 'asc')->paginate(200);
            list($posts, $lastPage) = $this->postsData($Thread_id, $currentPage, $request->search_content); //更好的分页sql语句
        } else {
            // $posts = $CurrentThread->posts()->orderBy('id', 'asc')->paginate(200);
            list($posts, $lastPage) = $this->postsData($Thread_id, $currentPage); //更好的分页sql语句
        }


        // if ($posts->currentPage() > 1) {
        //     $posts->appendPost0($CurrentThread->posts()->first()); //为第2页及之后增加0楼
        // }

        //如果有提供binggan，为每个post输入binggan，用来判断is_your_post（为前端提供是否是用户自己帖子的判据）
        //如果有提供binggan，为每个thread输入binggan，用来判断is_your_thread（为前端提供是否是用户自己帖子的判据）
        if ($request->query('binggan')) {
            foreach ($posts as $post) {
                $post->setBinggan($request->query('binggan'));
                $post->setUserID($user->id);
            }
            $CurrentThread->setBinggan($request->query('binggan'));
            $CurrentThread->makeVisible('is_your_thread');
        }

        //为反精分帖子加上created_binggan_hash
        if ($CurrentThread->anti_jingfen) {
            $posts->append('created_binggan_hash');
        }

        //为超管加入发帖者饼干显示
        if ($user && $user->admin == 99) {
            $posts->makeVisible('created_binggan');
        }

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

        if ($user) {
            //有正常看帖行为则清除redis灌水检查记录
            $user->waterClear('view_post', $request->ip());
            //检查成就（小火锅终末旅行）
            UserMedalRecord::check_end_travel($Thread_id, $user);
        }
        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'forum_data' => $CurrentForum,
            'thread_data' => $CurrentThread,
            'posts_data' => array(
                "currentPage" => intval($currentPage),
                "data" => $posts,
                "lastPage" => $lastPage,
            ),
            // 'posts_data' => $posts,
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
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        //退回发主题用户的olo
        $olo = 0;
        if ($CurrentThread->title_color != null && $CurrentThread->title_color != "#212529") {
            $olo += 500;
        }
        if ($CurrentThread->locked_by_coin > 0) {
            $olo += 500;
        }
        if ($CurrentThread->is_private == true) {
            $olo += 500;
        }
        if ($CurrentThread->vote_question_id != null) {
            $olo += 1000;
        }
        if ($CurrentThread->gamble_question_id != null) {
            $olo += 500;
        }
        if ($olo > 0) {
            $user->coinChange(
                'normal', //记录类型
                [
                    'olo' => $olo,
                    'content' => '延时发送主题撤回后退回olo（收费功能）',
                ]
            ); //通过统一接口、记录操作  
        }

        if ($CurrentThread->hongbao_id != null) {
            $hongbao = Hongbao::find($CurrentThread->hongbao_id);
            //需要获取发表主题当时的税率，以正确退回olo
            $tax_rate = GlobalSetting::get_tax('normal', Carbon::parse($CurrentThread->create_at));
            $user->coinChange(
                'normal', //记录类型
                [
                    'olo' => ceil($hongbao->olo_remains * $tax_rate),
                    'content' => '延时发送主题撤回后退回olo（红包）',
                ]
            ); //通过统一接口、记录操作  
            $hongbao->olo_remains = 0;
            $hongbao->num_remains = 0;
            $hongbao->save();
        }

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'thread_id' => $Thread_id,
        ]);
    }

    //改变标题颜色
    public function change_color(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'thread_id' => 'required|integer',
            'color' => 'required|string',
        ]);

        //验证是否颜色代码
        if (preg_match('/^#([0-9a-fA-F]{6})$/', $request->color) == false) {
            return response()->json([
                'code' => 422,
                'message' => '颜色代码有误，请确认。',
            ]);
        }


        $CurrentThread = Thread::find($request->thread_id);
        if (!$CurrentThread) {
            return response()->json([
                'code' => ResponseCode::THREAD_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::THREAD_NOT_FOUND],
            ]);
        }

        $user = $request->user;
        //只有主题发起者才能改标题颜色
        if ($CurrentThread->created_binggan != $user->binggan) {
            return response()->json([
                'code' => ResponseCode::USER_CANNOT,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_CANNOT],
                'user' => $user,
            ]);
        }

        try {
            DB::beginTransaction();
            $CurrentThread->title_color = $request->color;
            $CurrentThread->save();
            $user->coinChange(
                'normal', //记录类型
                [
                    'olo' => -500,
                    'content' => '标题改色',
                    'thread_id' => $CurrentThread->id,
                    'thread_title' => $CurrentThread->title,
                ]
            ); //扣除用户相应olo（通过统一接口、记录操作）
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '已变更标题颜色为' . $request->color,
            'thread_id' => $request->thread_id,
            'color' => $request->color
        ]);
    }
}
