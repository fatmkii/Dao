<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        ]);



        // $user = User::where('binggan', $request->binggan)->first();
        $user = $request->user;
        // if (!$user) {
        //     return response()->json(
        //         [
        //             'code' => ResponseCode::USER_NOT_FOUND,
        //             'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
        //         ],
        //     );
        // }

        //如果回帖频率过高，返回错误
        if (Redis::GET('new_post_record_' . $request->binggan) >= 10 && $user->admin == 0) {
            return response()->json([
                'code' => ResponseCode::POST_TOO_MANY,
                'message' => ResponseCode::$codeMap[ResponseCode::POST_TOO_MANY] . '为防止刷屏，每1分钟最多回帖10次',
            ]);
        }

        //如果饼干被ban，直接返回错误
        // if ($user->is_banned) {
        //     return response()->json(
        //         [
        //             'code' => ResponseCode::USER_BANNED,
        //             'message' => ResponseCode::$codeMap[ResponseCode::USER_BANNED],
        //             'data' => [
        //                 'binggan' => $user->binggan,
        //             ],
        //         ],
        //         401
        //     );
        // }

        //查询饼干是否在封禁期
        // if ($user->lockedTTL) {
        //     $lockTTL_hours = intval($user->lockedTTL / 3600) + 1;
        //     return response()->json(
        //         [
        //             'code' => ResponseCode::USER_LOCKED,
        //             'message' => ResponseCode::$codeMap[ResponseCode::USER_LOCKED] . '，将于' . $lockTTL_hours . '小时后解封',
        //         ],
        //     );
        // }

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
            // $post->floor = Post::suffix(intval($request->thread_id / 10000))->where('thread_id', $request->thread_id)->count();
            // $post->save();
            $thread = $post->thread;
            // $thread = Thread::where('id', $post->thread->id)->sharedLock()->first();//用sharelock防止posts_num错误
            // $thread->posts_num++;
            // $post->floor = $thread->posts_num;
            $thread->posts_num = $thread->posts->count();
            $post->floor = $thread->posts->count();
            $thread->save();
            $post->save();
            $user->coin += 10; //回复+10奥利奥
            $user->save();
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试',
            ]);
        }

        //用redis记录回频率。限定1分钟内只能回帖10次。
        if (Redis::exists('new_post_record_' . $request->binggan)) {
            Redis::incr('new_post_record_' . $request->binggan);
        } else {
            Redis::setex('new_post_record_' . $request->binggan,  60, 1);
        }

        //清除redis的posts缓存
        // for ($i = 1; $i <= ceil($thread->posts_num / 200); $i++) {
        //     Cache::forget('threads_cache_' . $thread->id . '_' . $i);
        // }
        // ProcessUserActive::dispatch(
        //     [
        //         'binggan' => $user->binggan,
        //         'user_id' => $user->id,
        //         'active' => '用户发表了新回帖',
        //         'thread_id' => $thread->id,
        //         'post_id' => $post->id,
        //     ]
        // );
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

        //判断饼干是否足够
        // $user = User::where('binggan', $request->binggan)->first();
        $user = $request->user;
        if ($user->coin < 300) {
            return response()->json(
                [
                    'code' => ResponseCode::COIN_NOT_ENOUGH,
                    'message' => ResponseCode::$codeMap[ResponseCode::COIN_NOT_ENOUGH],
                    'data' => [
                        'post_id' => $id,
                    ]
                ],
            );
        }

        $post->is_deleted = 1;
        $post->save();
        $user->coin -= 300; //删除帖子扣除300奥利奥
        $user->save();

        //清除redis的posts缓存
        // $thread = $post->thread;
        // for ($i = 1; $i <= ceil($thread->posts_num / 200); $i++) {
        //     Cache::forget('threads_cache_' . $thread->id . '_' . $i);
        // }
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

        //判断饼干是否足够
        // $user = User::where('binggan', $request->binggan)->first();
        $user = $request->user;
        if ($user->coin < 300) {
            return response()->json(
                [
                    'code' => ResponseCode::COIN_NOT_ENOUGH,
                    'message' => ResponseCode::$codeMap[ResponseCode::COIN_NOT_ENOUGH],
                    'data' => [
                        'post_id' => $id,
                    ]
                ],
            );
        }

        $post->is_deleted = 0;
        $post->save();
        $user->coin -= 300; //删除帖子扣除300奥利奥
        $user->save();

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

        // $user = User::where('binggan', $request->binggan)->first();
        $user = $request->user;
        // if (!$user) {
        //     return response()->json(
        //         [
        //             'code' => ResponseCode::USER_NOT_FOUND,
        //             'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
        //         ],
        //     );
        // }
        //如果饼干被ban，直接返回错误
        // if ($user->is_banned) {
        //     return response()->json(
        //         [
        //             'code' => ResponseCode::USER_BANNED,
        //             'message' => ResponseCode::$codeMap[ResponseCode::USER_BANNED],
        //             'data' => [
        //                 'binggan' => $user->binggan,
        //             ],
        //         ],
        //         401
        //     );
        // }

        //查询饼干是否在封禁期
        // if ($user->lockedTTL) {
        //     $lockTTL_hours = intval($user->lockedTTL / 3600) + 1;
        //     return response()->json(
        //         [
        //             'code' => ResponseCode::USER_LOCKED,
        //             'message' => ResponseCode::$codeMap[ResponseCode::USER_LOCKED] . '，将于' . $lockTTL_hours . '小时后解封',
        //         ],
        //     );
        // }

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
            $post->random_head = random_int(1, 40);
            // $post->floor = Post::suffix(intval($request->thread_id / 10000))->where('thread_id', $request->thread_id)->count();
            // $post->save();

            $thread = $post->thread;
            $thread->posts_num++;
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

        //清除redis的posts缓存
        // for ($i = 1; $i <= ceil($thread->posts_num / 200); $i++) {
        //     Cache::forget('threads_cache_' . $thread->id . '_' . $i);
        // }
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
