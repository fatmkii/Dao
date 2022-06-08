<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\CoinException;
use Illuminate\Support\Facades\DB;
use App\Models\Forum;
use App\Models\Post;
use App\Models\Thread;
use Illuminate\Database\QueryException;
use App\Common\ResponseCode;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Jobs\ProcessUserActive;
use App\Jobs\ProcessIncomeStatement;
use Carbon\Carbon;

class PostController extends Controller
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
            'thread_id' => 'required|integer',
            'content' => 'required|string|max:20000',
            'nickname' => '',
            'post_with_admin' => 'boolean',
            'new_post_key' => 'required|string',
            'timestamp' => 'integer',
        ]);

        $user = $request->user;

        //灌水检查
        $water_check = $user->waterCheck('new_post', $request->ip(), $request->thread_id);
        if ($water_check != 'ok') return $water_check;


        //确认是否脚本机器人发帖
        if ($request->new_post_key != md5($request->thread_id . $request->binggan)) {
            ProcessUserActive::dispatch(
                [
                    'binggan' => $user->binggan,
                    'user_id' => $user->id,
                    'active' => '怀疑用户用脚本刷帖(key错误)',
                    'thread_id' => $request->thread_id,
                    'content' => 'ip:' . $request->ip(),
                ]
            );
            return response()->json([
                'code' => ResponseCode::POST_ROBOT,
                'message' => ResponseCode::$codeMap[ResponseCode::POST_ROBOT],
            ]);
        }

        $thread = Thread::find($request->thread_id);
        if (!$thread || $thread->is_delay == 1 || $thread->is_deleted != 0) {
            return response()->json([
                'code' => ResponseCode::THREAD_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::THREAD_NOT_FOUND],
            ]);
        }

        $forum = Forum::find($request->forum_id);
        if (!$forum) {
            return response()->json([
                'code' => ResponseCode::FORUM_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::FORUM_NOT_FOUND],
            ]);
        }

        //确认是否冒充管理员发帖
        if (
            $request->post_with_admin == true &&
            !in_array($request->forum_id, json_decode($user->AdminPermissions->forums))
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }

        //判断是否达到可以访问板块的最少奥利奥
        if ($forum->accessible_coin > 0) {
            if ($user->coin < $forum->accessible_coin && $user->admin == 0) {
                return response()->json([
                    'code' => ResponseCode::THREAD_UNAUTHORIZED,
                    'message' => sprintf("本小岛需要拥有大于%u奥利奥才能查看喔", $forum->accessible_coin),
                ]);
            }
        }

        //判断奥利奥锁定权限贴
        if ($thread->locked_by_coin > 0) {
            if ($user->coin < $thread->locked_by_coin && $user->admin == 0) {
                return response()->json([
                    'code' => ResponseCode::THREAD_UNAUTHORIZED,
                    'message' => sprintf("本贴需要拥有大于%u奥利奥才能查看喔", $thread->locked_by_coin),
                ]);
            }
        }

        //判断是否私密主题 
        if ($thread->is_private == true) {
            if ($user->binggan != $thread->created_binggan && $user->admin == 0) {
                return response()->json([
                    'code' => ResponseCode::THREAD_IS_PRIVATE,
                    'message' => '本贴是私密主题，只有发帖者可以查看喔',
                ]);
            }
        }

        //执行追加新回复流程
        try {
            DB::beginTransaction();
            $post = new Post;
            $post->setSuffix(intval($request->thread_id / 10000));
            $post->created_binggan = $request->binggan;
            $post->forum_id = $request->forum_id;
            $post->thread_id = $request->thread_id;
            $post->content = $request->content;
            $post->nickname = $request->nickname;
            $post->created_by_admin = $request->post_with_admin  ? 1 : 0;
            $post->created_ip = $request->ip();
            $post->random_head = random_int(0, 39);
            $post->millisecond = intval(substr($request->timestamp, -3, 3));

            $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
            $post->floor = $thread->posts_num;

            $thread->save();
            $post->save();

            // $user->coin += 10; //回复+10奥利奥
            $user->coinChange(
                'post', //记录类型
                [
                    'olo' => 10,
                    'content' => '回帖',
                ]
            ); //回复+10奥利奥（通过统一接口、记录操作）
            $user->nickname = $request->nickname; //记录昵称
            $user->save();
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试',
            ]);
        }

        // if (
        //     $request->thread_id == 21574
        //     && $request->content == "新春快乐"
        // ) {
        //     CommonController::post_hongbao($request, $thread, $post); //执行送红包流程
        // }

        //用redis记录回频率。
        $user->waterRecord('new_post', $request->ip());

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '发表回复成功！奥利奥+10',
                'data' => [
                    'forum_id' => $request->forum_id,
                    'thread_id' => $request->thread_id,
                    'post_id' => $post->id,
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
    public function show($id)
    {
        //
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

    public function destroy(Request $request, $id)
    {
        $request->validate([
            'binggan' => 'required|string',
            'thread_id' => 'required|integer',
        ]);

        $post = Post::suffix(intval($request->thread_id / 10000))->find($id);
        //判断删帖操作者饼干和post饼干是否相同
        if ($post->created_binggan != $request->binggan) {
            return response()->json(
                [
                    'code' => ResponseCode::USER_UNAUTHORIZED,
                    'message' => '饼干错误',
                    'data' => [
                        'post_id' => $id,
                        'created_binggan' => $post->created_binggan,
                        '$request->binggan' => $request->binggan,
                    ]
                ],
            );
        }

        try {
            DB::beginTransaction();
            //判断饼干是否足够
            $user = $request->user;
            // $user->coinConsume(300); //删除帖子扣除300奥利奥
            $thread = $post->thread;
            $user->coinChange(
                'normal', //记录类型
                [
                    'olo' => -300,
                    'content' => '删除帖子',
                    'thread_id' => $thread->id,
                    'thread_title' => $thread->title,
                    'post_id' => $post->id,
                    'floor' => $post->floor,
                ]
            ); //删除帖子扣除300奥利奥（通过统一接口、记录操作）


            $post->is_deleted = 1;
            $post->save();
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

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户删除了回帖',
                'thread_id' => $request->thread_id,
                'post_id' => $post->id,
            ]
        );
        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '删除回复成功！',
                'data' => [
                    'post_id' => $id,
                    '$post' => $post
                ]
            ],
        );
    }

    public function recover(Request $request, $id)
    {
        $request->validate([
            'binggan' => 'required|string',
            'thread_id' => 'required|integer',
        ]);

        $post = Post::suffix(intval($request->thread_id / 10000))->find($id);
        //判断删帖操作者饼干和post饼干是否相同
        if ($post->created_binggan != $request->binggan) {
            return response()->json(
                [
                    'code' => ResponseCode::USER_UNAUTHORIZED,
                    'message' => '饼干错误',
                    'data' => [
                        'post_id' => $id,
                        'created_binggan' => $post->created_binggan,
                        '$request->binggan' => $request->binggan,
                    ]
                ],
            );
        }

        //判断是否可以恢复
        if ($post->is_deleted != 1) {
            return response()->json(
                [
                    'code' => ResponseCode::POST_UNAUTHORIZED,
                    'message' => '该帖子不能恢复！',
                    'data' => [
                        'post_id' => $id,
                    ]
                ],
            );
        }


        try {
            DB::beginTransaction();
            //判断饼干是否足够
            $user = $request->user;
            // $user->coinConsume(300); //恢复帖子扣除300奥利奥
            $thread = $post->thread;
            $user->coinChange(
                'normal', //记录类型
                [
                    'olo' => -300,
                    'content' => '恢复帖子',
                    'thread_id' => $thread->id,
                    'thread_title' => $thread->title,
                    'post_id' => $post->id,
                    'floor' => $post->floor,
                ]
            ); //恢复帖子扣除300奥利奥（通过统一接口、记录操作）

            $post->is_deleted = 0;
            $post->save();
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

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户恢复了已删除的回帖',
                'thread_id' => $request->thread_id,
                'post_id' => $post->id,
            ]
        );
        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '恢复回帖成功！',
                'data' => [
                    'post_id' => $id,
                    '$post' => $post
                ]
            ],
        );
    }

    public function create_roll(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'forum_id' => 'required|integer',
            'thread_id' => 'required|integer',
            'roll_name' => 'nullable|string',
            'roll_event' => 'nullable|string',
            'roll_num' => 'required|integer|max:1000|min:1',
            'roll_range' => 'required|integer|max:100000000|min:1',
        ]);

        $user = $request->user;

        //生成roll点结果
        $roll_result_arr = array();
        for ($i = 0; $i < $request->roll_num; $i++) {
            array_push($roll_result_arr, rand(1, $request->roll_range));
        }
        $roll_result_str = sprintf(
            "%s d %s = 「%s」.",
            $request->roll_num,
            $request->roll_range,
            join(", ", $roll_result_arr),
        );;
        if ($request->roll_event) {
            $roll_result_str = substr_replace($roll_result_str, "「" . $request->roll_event . "」的结果", 0, 0);
        }
        if ($request->roll_name) {
            $roll_result_str = substr_replace($roll_result_str, "「" . $request->roll_name . "」，", 0, 0);
        }

        //执行追加新roll点的流程
        try {
            DB::beginTransaction();
            $post = new Post;
            $post->setSuffix(intval($request->thread_id / 10000));
            $post->created_binggan = $request->binggan;
            $post->forum_id = $request->forum_id;
            $post->thread_id = $request->thread_id;
            $post->content = $roll_result_str;
            $post->created_by_admin = 2; //0=一般用户 1=管理员发布，2=系统发布
            $post->nickname = 'Roll点系统';
            $post->created_ip = $request->ip();
            $post->random_head = random_int(0, 39);

            $thread = $post->thread;
            $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
            $post->floor = $thread->posts_num;
            $thread->save();
            $post->save();
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试',
            ]);
        }

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户roll点了',
                'thread_id' => $request->thread_id,
                'post_id' => $post->id,
            ]
        );
        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => 'roll点成功！',
                'data' => [
                    'forum_id' => $request->forum_id,
                    'thread_id' => $request->thread_id,
                    'post_id' => $post->id,
                ]
            ],
        );
    }
}
