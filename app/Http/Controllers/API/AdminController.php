<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Common\ResponseCode;
use App\Jobs\ProcessUserActive;
use App\Models\AnnoucementMessages;
use App\Models\Forum;
use App\Models\HongbaoPost;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function thread_delete(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|integer',
            'content' => 'required|string|max:255'
        ]);

        $user = $request->user();

        $thread = Thread::find($request->thread_id);
        if (!$thread) {
            return response()->json([
                'code' => ResponseCode::THREAD_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::THREAD_NOT_FOUND],
            ]);
        }

        //确认是否拥有该版面的管理员权限
        if (
            !($user->tokenCan('forum_admin') && in_array($thread->forum_id, json_decode($user->AdminPermissions->forums)))
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }


        $thread->is_deleted = 2;
        $thread->save();
        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员删除了主题',
                'thread_id' => $thread->id,
                'content' => $request->content,
            ]
        );

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '该主题已删除。',
            'data' => [
                'thread_id' => $thread->id,
            ],
        ]);
    }

    public function thread_set_top(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|integer',
        ]);

        $user = $request->user();

        $thread = Thread::find($request->thread_id);
        if (!$thread) {
            return response()->json([
                'code' => ResponseCode::THREAD_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::THREAD_NOT_FOUND],
            ]);
        }

        //确认是否拥有该版面的管理员权限
        if (
            !($user->tokenCan('forum_admin') && in_array($thread->forum_id, json_decode($user->AdminPermissions->forums)))
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }


        $thread->sub_id = 10;
        $thread->save();

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员置顶了主题',
                'thread_id' => $thread->id,
            ]
        );

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '该主题已经置顶',
            'data' => [
                'thread_id' => $thread->id,
            ],
        ]);
    }

    public function thread_cancel_top(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|integer',
        ]);

        $user = $request->user();

        $thread = Thread::find($request->thread_id);
        if (!$thread) {
            return response()->json([
                'code' => ResponseCode::THREAD_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::THREAD_NOT_FOUND],
            ]);
        }

        //确认是否拥有该版面的管理员权限
        if (
            !($user->tokenCan('forum_admin') && in_array($thread->forum_id, json_decode($user->AdminPermissions->forums)))
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }

        $thread->sub_id = 0;
        $thread->save();
        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员取消了置顶主题',
                'thread_id' => $thread->id,
            ]
        );
        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '该主题已经取消置顶',
            'data' => [
                'thread_id' => $thread->id,
            ],
        ]);
    }

    public function post_delete(Request $request, $post_id)
    {
        $request->validate([
            'thread_id' => 'required|integer',
            'content' => 'required|string|max:255',
            'reduce_olo' => 'required|boolean'
        ]);

        $user = $request->user();

        $post = Post::suffix(intval($request->thread_id / 10000))->find($post_id);
        if (!$post) {
            return response()->json([
                'code' => ResponseCode::POST_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::POST_NOT_FOUND],
            ]);
        }

        //确认是否已经删除过了
        if ($post->is_deleted != 0) {
            return response()->json([
                'code' => ResponseCode::DEFAULT,
                'message' => '该帖子已经被删除过了',
            ]);
        }

        //确认是否拥有该版面的管理员权限
        if (
            !($user->tokenCan('forum_admin') && in_array($post->forum_id, json_decode($user->AdminPermissions->forums)))
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }

        $post->is_deleted = 2;
        if ($post->battle_id != null) {
            $post->battle_id = null;
        }
        if ($post->hongbao_id != null) {
            HongbaoPost::where('id', $post->hongbao_id)->update(['deleted_at' => Carbon::now()]);
            $post->hongbao_id = null;
        }
        $post->save();


        if ($request->reduce_olo == True) {
            //扣除被删帖用户的olo 500个
            $user_to_delete = User::where('binggan', $post->created_binggan)->first();
            if ($user_to_delete) {
                $user_to_delete->coinChange(
                    'normal', //记录类型
                    [
                        'olo' => -500,
                        'content' => '被管理员删除帖子并扣除olo',
                        'thread_id' => $post->thread_id,
                        'thread_title' => Thread::where('id', $post->thread_id)->value('title'),
                        'post_id' => $post->id,
                        'floor' => $post->floor,
                    ],
                    true //$ignore_olo_0 = true，即使用户olo不足也强制扣除
                ); //扣除奥利奥（通过统一接口、记录操作）
            }
        }

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员删除了帖子',
                'thread_id' => $request->thread_id,
                'post_id' => $post->id,
                'content' => $request->content,
            ]
        );

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '该帖子已删除。',
            'data' => [
                'post_id' => $post->id,
            ],
        ]);
    }

    public function post_recover(Request $request, $post_id)
    {
        $request->validate([
            'thread_id' => 'required|integer',
            'content' => 'required|string|max:255'
        ]);

        $user = $request->user();

        $post = Post::suffix(intval($request->thread_id / 10000))->find($post_id);
        if (!$post) {
            return response()->json([
                'code' => ResponseCode::POST_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::POST_NOT_FOUND],
            ]);
        }

        //确认是否拥有该版面的管理员权限
        if (
            !($user->tokenCan('forum_admin') && in_array($post->forum_id, json_decode($user->AdminPermissions->forums)))
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }

        $post->is_deleted = 0;
        $post->save();

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员恢复了已删除的帖子',
                'thread_id' => $request->thread_id,
                'post_id' => $post->id,
                'content' => $request->content,
            ]
        );

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '该帖子已恢复。',
            'data' => [
                'post_id' => $post->id,
            ],
        ]);
    }


    public function post_delete_all(Request $request)
    {
        $request->validate([
            'post_id' => 'required|Integer',
            'thread_id' => 'required|integer',
            'content' => 'required|string|max:255',
            'reduce_olo' => 'required|boolean'
        ]);

        $user = $request->user();

        $post = Post::suffix(intval($request->thread_id / 10000))->find($request->post_id);
        if (!$post) {
            return response()->json([
                'code' => ResponseCode::POST_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::POST_NOT_FOUND],
            ]);
        }

        //确认是否拥有该版面的管理员权限
        if (
            !($user->tokenCan('forum_admin') && in_array($post->forum_id, json_decode($user->AdminPermissions->forums)))
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }

        $posts_num = Post::suffix(intval($request->thread_id / 10000))
            ->where('thread_id', $request->thread_id)
            ->where('created_binggan', $post->created_binggan)
            ->where('is_deleted', 0)
            ->count();
        //确认是否已经删除过了
        if ($posts_num == 0) {
            return response()->json([
                'code' => ResponseCode::DEFAULT,
                'message' => '该用户的全部帖子都被删除过了',
            ]);
        }

        // Post::suffix(intval($request->thread_id / 10000))
        //     ->where('thread_id', $request->thread_id)
        //     ->where('created_binggan', $post->created_binggan)
        //     ->where('is_deleted', 0)
        //     ->chunk(5, function ($posts_to_delete) {
        //         foreach ($posts_to_delete as $post_to_delete) {
        //             $post_to_delete->is_deleted = 2;
        //             $post_to_delete->save();
        //         }
        //     });
        Post::suffix(intval($request->thread_id / 10000))
            ->where('thread_id', $request->thread_id)
            ->where('created_binggan', $post->created_binggan)
            ->where('is_deleted', 0)
            ->update(['is_deleted' => 2]);

        if ($request->reduce_olo == True) {
            //扣除被删帖用户的olo 500个/每个帖子（上限5000）
            $olo = $posts_num >= 10 ? 5000 : $posts_num * 500; //注意这里是个正数
            $user_to_delete_all = User::where('binggan', $post->created_binggan)->first();
            if ($user_to_delete_all) {
                $user_to_delete_all->coinChange(
                    'normal', //记录类型
                    [
                        'olo' => -$olo,
                        'content' => '被管理员删除帖子并扣除olo',
                        'thread_id' => $post->thread_id,
                        'thread_title' => Thread::where('id', $post->thread_id)->value('title'),
                        'post_id' => $post->id,
                        'floor' => $post->floor,
                    ],
                    true //$ignore_olo_0 = true，即使用户olo不足也强制扣除
                ); //扣除奥利奥（通过统一接口、记录操作）
            }
        }

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员删除该用户全部的回帖',
                'thread_id' => $request->thread_id,
                'binggan_target' => $post->created_binggan,
                'content' => $request->content,
            ]
        );

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' =>  sprintf('该作者全部帖子已删除。一共有%d个帖子', $posts_num),
        ]);
    }

    public function user_ban(Request $request)
    {
        $request->validate([
            'post_id' => 'required|Integer',
            'thread_id' => 'required|integer',
            'content' => 'required|string|max:255'
        ]);

        $user = $request->user();

        $post = Post::suffix(intval($request->thread_id / 10000))->find($request->post_id);
        if (!$post) {
            return response()->json([
                'code' => ResponseCode::POST_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::POST_NOT_FOUND],
            ]);
        }

        //确认是否拥有该版面的管理员权限
        if (
            !($user->tokenCan('admin') && in_array($post->forum_id, json_decode($user->AdminPermissions->forums)))
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }

        $user_to_ban = User::where('binggan', $post->created_binggan)->first();
        if (!$user_to_ban) {
            return response()->json([
                'code' => ResponseCode::USER_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
            ]);
        }

        $user_to_ban->is_banned = true;
        $user_to_ban->save();
        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员碎了饼干',
                'thread_id' => $request->thread_id,
                'post_id' => $request->post_id,
                'binggan_target' => $user_to_ban->binggan,
                'content' => $request->content,
            ]
        );
        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '已碎饼干。阿弥陀佛，善哉善哉。',
        ]);
    }

    public function user_lock(Request $request)
    {
        $request->validate([
            'post_id' => 'required|Integer',
            'thread_id' => 'required|integer',
            'content' => 'required|string|max:255'
        ]);

        $user = $request->user();

        $post = Post::suffix(intval($request->thread_id / 10000))->find($request->post_id);
        if (!$post) {
            return response()->json([
                'code' => ResponseCode::POST_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::POST_NOT_FOUND],
            ]);
        }

        //确认是否拥有该版面的管理员权限
        if (
            !($user->tokenCan('forum_admin') && in_array($post->forum_id, json_decode($user->AdminPermissions->forums)))
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }

        $user_to_lock = User::where('binggan', $post->created_binggan)->first();
        if (!$user_to_lock) {
            return response()->json([
                'code' => ResponseCode::USER_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
            ]);
        }


        if ($user_to_lock->locked_until != null && $user_to_lock->locked_until > Carbon::now()) {
            return response()->json([
                'code' => ResponseCode::DEFAULT,
                'message' => '该饼干已经是封禁状态了。封禁期到：' . $user_to_lock->locked_until,
            ]);
        }

        // $user_to_lock->locked_until = Carbon::now()->addDays(3);
        $user_to_lock->locked_count += 1;
        $user_to_lock->locked_until = Carbon::now()->addDays(3 * $user_to_lock->locked_count); //每封禁一次，多3天
        if ($user_to_lock->locked_count >= 4) {
            //如果被锁定超过3次（≥4），则碎饼干
            $user_to_lock->is_banned = true;
            $msg = '该饼干已被累计封禁4次，已永久碎饼干。';
        } else {
            $msg = sprintf('该饼干已封禁%d天。', $user_to_lock->locked_count * 3);
        }
        $user_to_lock->save();
        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员封禁了饼干',
                'thread_id' => $request->thread_id,
                'post_id' => $request->post_id,
                'binggan_target' => $user_to_lock->binggan,
                'content' => $request->content,
            ]
        );
        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => $msg,
        ]);
    }

    public function set_banner(Request $request)
    {
        $request->validate([
            'forum_id' => 'required|Integer',
            'banners' => 'required|json|max:5000',
        ]);

        $user = $request->user();

        $forum = Forum::find($request->forum_id);
        if (!$forum) {
            return response()->json([
                'code' => ResponseCode::FORUM_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::FORUM_NOT_FOUND],
            ]);
        }

        //确认是否拥有该版面的管理员权限
        if (
            !($user->tokenCan('forum_admin') && in_array($forum->id, json_decode($user->AdminPermissions->forums)))
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }

        try {
            DB::beginTransaction();
            $forum->banners = $request->banners;
            $forum->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        //要清除板块的缓存
        Cache::forget('forums_cache');

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员更新了版头。板块：' . $request->forum_id,
                'content' => '版头字符串长度：' . mb_strlen($request->banners),
            ]
        );


        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '更新版头成功！',
        ]);
    }

    public function check_post(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|Integer',
            'post_id' => 'required|Integer',
        ]);

        $user = $request->user();

        $post = Post::suffix(intval($request->thread_id / 10000))->find($request->post_id);
        if (!$post) {
            return response()->json([
                'code' => ResponseCode::POST_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::POST_NOT_FOUND],
            ]);
        }

        //确认是否拥有该版面的管理员权限
        if (
            !($user->tokenCan('senior_admin') && in_array($post->forum_id, json_decode($user->AdminPermissions->forums)))
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }

        $user_target = User::where('binggan', $post->created_binggan)->first();
        if (!$user_target) {
            return response()->json([
                'code' => ResponseCode::USER_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
            ]);
        }

        $user_status = "";
        if ($user_target->is_banned) {
            $user_status = '已被碎了';
        } else {
            if ($user_target->locked_until) {
                if (Carbon::parse($user_target->locked_until) > Carbon::now()) {
                    $user_status = '被封禁至' . $user_target->locked_until;
                } else {
                    $user_status = '状态正常';
                }
            } else {
                $user_status = '状态正常';
            }
        }

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '已经查到用户信息',
            'locked_count' => $user_target->locked_count,
            'user_status' => $user_status,
        ]);
    }

    //已废弃
    // public function check_user_post(Request $request)
    // {
    //     $request->validate([
    //         'binggan' => 'required_without:IP|string',
    //         'IP' => 'required_without:binggan|ipv4',
    //         'page' => 'required|integer',
    //         'database_post_num' => 'required|integer',
    //     ]);

    //     //根据饼干查询发帖记录
    //     if ($request->query('binggan') != null) {
    //         $user_to_check = User::where('binggan', $request->query('binggan'))->first();
    //         if (!$user_to_check) {
    //             return response()->json([
    //                 'code' => ResponseCode::USER_NOT_FOUND,
    //                 'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
    //             ]);
    //         }
    //         $posts = Post::suffix($request->query('database_post_num'))
    //             ->where('created_binggan', $request->query('binggan'))->paginate(200);
    //     }
    //     //根据IP查询发帖记录
    //     if ($request->query('IP') != null) {
    //         $posts = Post::suffix($request->query('database_post_num'))
    //             ->where('created_IP', $request->query('IP'))->paginate(200);
    //     }

    //     $posts->makeVisible('created_IP');
    //     $posts->makeVisible('created_binggan');

    //     return response()->json([
    //         'code' => ResponseCode::SUCCESS,
    //         'posts_data' => $posts,
    //     ]);
    // }

    //暂停使用
    // public function check_jingfen(Request $request)
    // {
    //     $request->validate([
    //         'thread_id' => 'required|integer',
    //         'content' => 'required|string',
    //     ]);

    //     $CurrentThread = Thread::find($request->thread_id);
    //     if (!$CurrentThread) {
    //         return response()->json([
    //             'code' => ResponseCode::THREAD_NOT_FOUND,
    //             'message' => ResponseCode::$codeMap[ResponseCode::THREAD_NOT_FOUND],
    //         ]);
    //     }

    //     //确认是否拥有该版面的管理员权限
    //     $user = $request->user();
    //     if (
    //         !in_array($CurrentThread->forum_id, json_decode($user->AdminPermissions->forums))
    //     ) {
    //         return response()->json(
    //             [
    //                 'code' => ResponseCode::ADMIN_UNAUTHORIZED,
    //                 'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
    //             ],
    //         );
    //     }

    //     $posts = $CurrentThread->posts()->orderBy('id', 'asc')->paginate(200);

    //     //为管理员加上created_binggan_hash
    //     $posts->append('created_binggan_hash');


    //     //临时打开主题的反精分标签
    //     $CurrentThread->anti_jingfen = 1;

    //     ProcessUserActive::dispatch(
    //         [
    //             'binggan' => $user->binggan,
    //             'user_id' => $user->id,
    //             'active' => '管理员查看了反精分',
    //             'thread_id' => $request->thread_id,
    //             'content' => $request->content,
    //         ]
    //     );

    //     return response()->json([
    //         'code' => ResponseCode::SUCCESS,
    //         // 'forum_data' => $CurrentForum,
    //         'thread_data' => $CurrentThread,
    //         'posts_data' => $posts,
    //         // 'random_heads' => $random_heads,
    //     ]);
    // }

    public function create_annoucement(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'type' => 'required|integer',
            'title' => 'required|string',
            'sub_title' => 'required|string',
            'content' => 'required|string|max:20000',
            'to_new_users' => 'required|boolean',
        ]);

        $user = $request->user();


        //确认是否拥有该版面的管理员权限
        if (
            !$user->tokenCan('admin')
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
            $annoucement_message = new AnnoucementMessages();
            $annoucement_message->sender_id = $user->id;
            $annoucement_message->type = $request->type;
            $annoucement_message->title = $request->title;
            $annoucement_message->sub_title = $request->sub_title;
            $annoucement_message->content = $request->content;
            $annoucement_message->to_new_users = $request->to_new_users;
            $annoucement_message->created_at = Carbon::now();
            $annoucement_message->save();

            if ($request->type == 1) {
                //如果是全体公告，则所有用户的new_msg设为true，以拉取消息
                User::all()->update(['new_msg', true]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }


        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员发布了公告',
                'content' => '标题：' . $request->title,
            ]
        );

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '已发布消息公告',
        ]);
    }

    public function show_annoucements(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'page' => 'integer|nullable',
            'per_page' => 'integer|nullable|max:100|min:1',
        ]);

        $annoucements = AnnoucementMessages::paginate($request->per_page);

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '已返回公告清单',
            'data' => $annoucements,
        ]);
    }

    public function del_annoucements(Request $request, $annoucement_id)
    {
        $request->validate([
            'binggan' => 'required|string',
            'annoucement_id' => 'required|integer',
        ]);

        $user = $request->user();

        //确认是否拥有该版面的管理员权限
        if (
            !$user->tokenCan('admin')
        ) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }

        $annoucement = AnnoucementMessages::find($annoucement_id);
        if (!$annoucement) {
            return response()->json([
                'code' => ResponseCode::ANNOUCEMENT_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::ANNOUCEMENT_NOT_FOUND],
            ]);
        }

        $annoucement->is_deleted = true;
        $annoucement->save();

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '已删除公告',
        ]);
    }
}
