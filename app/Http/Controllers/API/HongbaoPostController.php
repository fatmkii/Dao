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
use Illuminate\Support\Facades\Redis;

class HongbaoPostController extends Controller
{
    public function create(Request $request)
    {
        if (Carbon::now()->between('2022/12/6 08:00:00', '2022/12/7 00:00:00')) {
            return response()->json([
                'code' => ResponseCode::DEFAULT,
                'message' => '今天8:00到24:00暂停大乱斗和红包。T_T',
            ]);
        }

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
            if ($user->getCoin() < $forum->accessible_coin && !(in_array($user->admin, [99, 20, 10]))) {
                return response()->json([
                    'code' => ResponseCode::THREAD_UNAUTHORIZED,
                    'message' => sprintf("本小岛需要拥有大于%u奥利奥才能查看喔", $forum->accessible_coin),
                ]);
            }
        }

        //判断奥利奥锁定权限贴
        if ($thread->locked_by_coin > 0) {
            if ($user->getCoin() < $thread->locked_by_coin && !(in_array($user->admin, [99, 20, 10]))) {
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


    public static function store(Request $request, Thread $thread, Post &$post_original)
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
                    //已经抢过的红包就什么都不做
                    return;
                }


                //用Redis记录，限制10秒内同一个IP不能领取同一个红包
                $key = sprintf('hongbao_post_%s_%s', $hongbao_item->id, $request->ip()); //格式：hongbao_post_红包ID_IP地址
                if (Redis::exists($key)) {
                    // $post_content = "10s内不能茄饼领同一个红包哦";
                    // $post_content = "To №" . $post_original->floor . "：" . $post_content;
                    // Post::create([
                    //     'created_binggan' => $request->binggan,
                    //     'forum_id' => $request->forum_id,
                    //     'thread_id' => $request->thread_id,
                    //     'content' => $post_content,
                    //     'nickname' => '红包结果',
                    //     'created_by_admin' => 2,
                    //     'created_IP' => $request->ip(),
                    // ]);
                    return;
                }

                //执行追加新回复流程
                try {
                    DB::beginTransaction();

                    $coin = 0; //红包金额
                    $post_content = ""; //红包回帖信息

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
                        $post_content = sprintf("恭喜抢到来自№%d楼的最后一个红包，有%d个奥利奥！", $hongbao->floor, $coin);
                    } else {
                        if ($hongbao->type == 1) {
                            //随机红包
                            $central = intval($hongbao->olo_remains / $hongbao->num_remains);
                            $coin = rand(0, $central * 2);
                        } elseif ($hongbao->type == 2) {
                            //定额红包
                            $coin = intval($hongbao->olo_total / $hongbao->num_total);
                        }
                        $post_content = sprintf("你抢到了来自№%d楼的%d个奥利奥！", $hongbao->floor,  $coin);
                    }
                    $post_content = "To №" . $post_original->floor . "：" . $post_content;


                    $message = "";
                    if ($hongbao->message) {
                        //$hongbao->message当是单一message时候不为null
                        $message = $hongbao->message;
                        $post_content = $post_content . '<br>——' . $message;
                    }

                    if ($hongbao->message_json) {
                        //$hongbao->message_json当是多选一message时候不为null
                        $message_array = json_decode($hongbao->message_json);
                        if (count($message_array) >= 1) {
                            $rand_key = array_rand($message_array);
                            $message = $message_array[$rand_key]; //从多个回复中随机抽出一个
                            $post_content = $post_content . '<br>——' . $message;
                        }
                    }

                    if ($hongbao_item->key_word_type == 3) {
                        //暗号红包时，把回复中的红包口令隐藏起来
                        $post_original->content = $keyword_prefix . "（暗号红包已隐藏口令）";
                        $post_original->save();
                    }

                    HongbaoPost::withTrashed()->where('id', $hongbao_item->id)->increment('olo_remains', -$coin); //直接用$hongbao->withTrashed()的话， SQL查询语句会失去全部where条件，导致变更所有记录。可能是laravel的bug
                    HongbaoPost::withTrashed()->where('id', $hongbao_item->id)->decrement('num_remains');
                    // $hongbao->increment('olo_remains', -$coin);
                    // $hongbao->increment('num_remains', -1);
                    // $hongbao->save();

                    $post = Post::create([
                        'created_binggan' => $request->binggan,
                        'forum_id' => $request->forum_id,
                        'thread_id' => $request->thread_id,
                        'content' => $post_content,
                        'nickname' => '红包结果',
                        'created_by_admin' => 2,
                        'created_IP' => $request->ip(),
                    ]);

                    $user->coinChange(
                        'normal', //记录类型
                        [
                            'olo' => $coin,
                            'content' => $message != "" ? '抢到红包——' . $message : '抢到红包',
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

                    //追加该IP的抢红包记录，限制同一IP抢同一个红包
                    $key = sprintf('hongbao_post_%s_%s', $hongbao_item->id, $request->ip()); //格式：hongbao_post_红包ID_IP地址
                    $ttl = 10; //限制30秒
                    Redis::setex($key, $ttl, 1);

                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    throw $e;
                }

                //检查成就
                $user_medal_record = $user->UserMedalRecord()->firstOrCreate();
                $user_medal_record->push_hongbao_in($coin);
            } else {
                return;
            }
        });
    }
}
