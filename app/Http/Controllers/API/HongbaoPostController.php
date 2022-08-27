<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Thread;
use App\Models\Forum;
use App\Models\Post;
use App\Common\ResponseCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Events\NewPostBroadcast;
use App\Exceptions\CoinException;
use App\Models\HongbaoPost;
use App\Models\HongbaoPostUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class HongbaoPostController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'forum_id' => 'required|integer',
            'thread_id' => 'required|integer',
            // 'content' => 'required|string|max:20000',
            // 'nickname' => '',
            // 'post_with_admin' => 'boolean',
            // 'new_post_key' => 'required|string',
            // 'timestamp' => 'integer',
        ]);

        $user = $request->user;


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

        //判断是否达到可以访问板块的最少奥利奥
        if ($forum->accessible_coin > 0) {
            if ($user->coin < $forum->accessible_coin && !(in_array($user->admin, [99, 10]))) {
                return response()->json([
                    'code' => ResponseCode::THREAD_UNAUTHORIZED,
                    'message' => sprintf("本小岛需要拥有大于%u奥利奥才能查看喔", $forum->accessible_coin),
                ]);
            }
        }

        //判断奥利奥锁定权限贴
        if ($thread->locked_by_coin > 0) {
            if ($user->coin < $thread->locked_by_coin && !(in_array($user->admin, [99, 10]))) {
                return response()->json([
                    'code' => ResponseCode::THREAD_UNAUTHORIZED,
                    'message' => sprintf("本贴需要拥有大于%u奥利奥才能查看喔", $thread->locked_by_coin),
                ]);
            }
        }

        //判断是否私密主题 
        if ($thread->is_private == true) {
            if ($user->binggan != $thread->created_binggan && $user->admin != 99) {
                return response()->json([
                    'code' => ResponseCode::THREAD_IS_PRIVATE,
                    'message' => '本贴是私密主题，只有发帖者可以查看喔',
                ]);
            }
        }

        //执行追加新回复流程
        try {

            DB::beginTransaction();
            // $post = new Post;
            // $post->setSuffix(intval($request->thread_id / 10000));
            // $post->created_binggan = $request->binggan;
            // $post->forum_id = $request->forum_id;
            // $post->thread_id = $request->thread_id;
            // $post->content = "来抢红包啦！";
            // $post->nickname = '红包系统';
            // $post->created_by_admin = 2;
            // $post->created_ip = $request->ip();
            // $post->random_head = random_int(0, 39);

            // $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
            // $post->floor = $thread->posts_num;

            // $thread->save();
            // $post->save();

            $post = Post::create([
                'created_binggan' => $request->binggan,
                'forum_id' => $request->forum_id,
                'thread_id' => $request->thread_id,
                'content' => "来抢红包啦！",
                'nickname' => '红包系统',
                'created_by_admin' => 2,
                'created_IP' => $request->ip(),
            ]);

            //追加红包贴
            $hongbao = HongbaoPost::create($request, $thread->id, $post->id, $post->floor);
            $post->hongbao_id = $hongbao->id;
            $post->save(); //前面要先save一次才有post_id

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        //广播发帖动作
        // broadcast(new NewPostBroadcast($request->thread_id, $post->id, $post->floor))->toOthers();
        $post->broadcast();

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '已发起红包！',
            ],
        );
    }


    public static function store(Request $request, Thread $thread, Post $post_original)
    {
        $user = $request->user;

        $hongbaos = HongbaoPost::where('thread_id', $thread->id)->get();
        if (!$hongbaos) {
            return;
        }

        $hongbaos->each(function ($hongbao_item, $key) use ($post_original, $request, $thread, $user) {

            $keyword_prefix = '--红包口令: '; //为了方便前端识别并屏蔽，增加前缀
            if (in_array($hongbao_item->type, [1, 2]) && $post_original->content == $keyword_prefix . $hongbao_item->key_word) {

                $hongbao_user_exists  = HongbaoPostUser::where('hongbao_post_id', $hongbao_item->id)->where('user_id', $user->id)->exists();
                if ($hongbao_user_exists) {
                    return;
                }

                //执行追加新回复流程
                try {
                    DB::beginTransaction();

                    $coin = 0; //红包金额
                    $message = ""; //红包回帖信息

                    $hongbao = HongbaoPost::lockForUpdate()->find($hongbao_item->id);
                    if (!$hongbao) {
                        //红包不能存在就直接返回
                        DB::rollBack();
                        return;
                    }
                    if ($hongbao->num_remains == 0) {
                        DB::rollBack();
                        return;
                    } elseif ($hongbao->num_remains == 1) {
                        $hongbao->delete(); //软删除
                        $coin = $hongbao->olo_remains;
                        $message = sprintf("恭喜抢到最后一个红包，有%d个奥利奥！", $coin);
                    } else {
                        if ($hongbao->type == 1) {
                            $central = intval($hongbao->olo_remains / $hongbao->num_remains);
                            $coin = rand(1, $central * 2);
                        } elseif ($hongbao->type == 2) {
                            $coin = intval($hongbao->olo_total / $hongbao->num_total);
                        }
                        $message = sprintf("你抢到了%d个奥利奥！",  $coin);
                    }
                    $message = "To №" . $post_original->floor . "：" . $message;
                    if ($hongbao->message) {
                        $message = $message . '<br>——' . $hongbao->message;
                    }

                    $hongbao->olo_remains -= $coin;
                    $hongbao->num_remains -= 1;
                    $hongbao->save();

                    // $post = new Post;
                    // $post->setSuffix(intval($request->thread_id / 10000));
                    // $post->created_binggan = $request->binggan;
                    // $post->forum_id = $request->forum_id;
                    // $post->thread_id = $request->thread_id;
                    // $post->content = $message;
                    // $post->nickname = '红包结果';
                    // $post->created_by_admin = 2; //0=一般用户 1=管理员发布，2=系统发布
                    // $post->created_ip = $request->ip();
                    // $post->random_head = random_int(0, 39);

                    // $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
                    // $post->floor = $thread->posts_num;

                    // $thread->save();
                    // $post->save();

                    $post = Post::create([
                        'created_binggan' => $request->binggan,
                        'forum_id' => $request->forum_id,
                        'thread_id' => $request->thread_id,
                        'content' => $message,
                        'nickname' => '红包结果',
                        'created_by_admin' => 2,
                        'created_IP' => $request->ip(),
                    ]);

                    $user->coinChange(
                        'normal', //记录类型
                        [
                            'olo' => $coin,
                            'content' => '抢到红包',
                            'thread_id' => $thread->id,
                            'thread_title' => $thread->title,
                            'post_id' => $post_original->id,
                            'floor' => $post_original->floor,
                        ]
                    ); //（通过统一接口、记录操作）
                    $user->save();

                    $hongbao_user = new HongbaoPostUser();
                    $hongbao_user->hongbao_post_id = $hongbao->id;
                    $hongbao_user->user_id = $user->id;
                    $hongbao_user->olo = $coin;
                    $hongbao_user->floor = $post_original->floor;
                    $hongbao_user->created_at = Carbon::now();
                    $hongbao_user->save();

                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            } else {
                return;
            }
        });
    }
}
