<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Hongbao;
use Illuminate\Http\Request;
use App\Common\ResponseCode;
use App\Models\HongbaoUser;
use App\Models\User;
use App\Models\Thread;
use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class HongbaoController extends Controller
{

    public function show(Request $request, $hongbao_id)
    {
        $hongbao = Hongbao::find($hongbao_id);
        if (!$hongbao) {
            return response()->json([
                'code' => ResponseCode::HONGBAO_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::HONGBAO_NOT_FOUND],
            ]);
        }

        $user = $request->user;
        if ($user) {
            $hongbao->setUserID($user->id);
        }

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'hongbao' => $hongbao,
        ]);
    }

    public static function store(Request $request, Thread $thread, Post $post_original)
    {
        $user = $request->user;

        $coin = 0; //红包金额
        $message = ""; //红包回帖信息

        $hongbao = Hongbao::find($thread->hongbao_id);
        if (!$hongbao) {
            return;
        }

        if ($hongbao->type == 1 && $post_original->content == $hongbao->key_word) {

            $hongbao_user_exists  = HongbaoUser::where('hongbao_id', $thread->hongbao_id)->where('user_id', $user->id)->exists();
            if ($hongbao_user_exists) {
                $message = "你已经领取过了，不要贪心喔！";
                $message = "To №" . $post_original->floor . "：" . $message;

                $post = new Post;
                $post->setSuffix(intval($request->thread_id / 10000));
                $post->created_binggan = $request->binggan;
                $post->forum_id = $request->forum_id;
                $post->thread_id = $request->thread_id;
                $post->content = $message;
                $post->nickname = '红包系统';
                $post->created_by_admin = 2; //0=一般用户 1=管理员发布，2=系统发布
                $post->created_ip = $request->ip();
                $post->random_head = random_int(0, 39);

                $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
                $post->floor = $thread->posts_num;

                $thread->save();
                $post->save();
                return;
            }

            //执行追加新回复流程
            try {
                DB::beginTransaction();

                $hongbao = Hongbao::lockForUpdate()->find($thread->hongbao_id);
                if ($hongbao->num_remains == 0) {
                    DB::rollBack();
                    return;
                } elseif ($hongbao->num_remains == 1) {
                    $coin = $hongbao->olo_remains;
                    $message = sprintf("恭喜抢到最后一个红包，有%d个奥利奥！", $coin);
                } else {
                    $central = intval($hongbao->olo_remains / $hongbao->num_remains);
                    $coin = rand(1, $central * 2);
                    $message = sprintf("你抢到了%d个奥利奥！", $coin);
                }
                $message = "To №" . $post_original->floor . "：" . $message;
                if ($hongbao->message) {
                    $message = $message . '<br>——' . $hongbao->message;
                }

                $hongbao->olo_remains -= $coin;
                $hongbao->num_remains -= 1;
                $hongbao->save();

                $post = new Post;
                $post->setSuffix(intval($request->thread_id / 10000));
                $post->created_binggan = $request->binggan;
                $post->forum_id = $request->forum_id;
                $post->thread_id = $request->thread_id;
                $post->content = $message;
                $post->nickname = '红包结果';
                $post->created_by_admin = 2; //0=一般用户 1=管理员发布，2=系统发布
                $post->created_ip = $request->ip();
                $post->random_head = random_int(0, 39);

                $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
                $post->floor = $thread->posts_num;

                $thread->save();
                $post->save();


                $user->coinChange(
                    'normal', //记录类型
                    [
                        'olo' => $coin,
                        'content' => '抢到红包',
                        'thread_id' => $thread->id,
                        'thread_title' => $thread->title,
                        'post_id' => $post->id,
                        'floor' => $post->floor,
                    ]
                ); //（通过统一接口、记录操作）
                $user->save();

                $hongbao_user = new HongbaoUser;
                $hongbao_user->hongbao_id = $hongbao->id;
                $hongbao_user->user_id = $user->id;
                $hongbao_user->olo = $coin;
                $hongbao_user->created_at = Carbon::now();
                $hongbao_user->save();

                DB::commit();
            } catch (QueryException $e) {
                DB::rollback();
            }
        } else {
            return;
        }
    }
}
