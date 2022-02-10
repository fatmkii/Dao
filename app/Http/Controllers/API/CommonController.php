<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Common\ResponseCode;
use App\Common\Captcha;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use App\Models\Post;
use App\Models\Thread;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

class CommonController extends Controller
{
    public function emoji_index()
    {
        $emoji = Cache::remember('emoji_cache',  24 * 3600, function () {
            return DB::table('emoji')->get();
        });
        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => ResponseCode::$codeMap[ResponseCode::SUCCESS],
            'data' => $emoji,
        ]);
    }

    public function subtitles_index()
    {
        $subtitles = Cache::remember('subtitles_cache',  24 * 3600, function () {
            return DB::table('subtitles')->get();
        });
        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => ResponseCode::$codeMap[ResponseCode::SUCCESS],
            'data' => $subtitles,
        ]);
    }

    public function random_heads_index()
    {
        $random_heads_index = Cache::remember('random_heads_index_cache',  24 * 3600, function () {
            return DB::table('random_heads')->get(['id', 'name']);
        });
        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => ResponseCode::$codeMap[ResponseCode::SUCCESS],
            'data' => $random_heads_index,
        ]);
    }

    public function random_heads_show($id)
    {
        $random_heads = Cache::remember('random_heads_cache_' . $id, 24 * 3600, function () use ($id) {
            return DB::table('random_heads')->find($id);
        });
        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => ResponseCode::$codeMap[ResponseCode::SUCCESS],
            'data' => $random_heads,
        ]);
    }

    public function get_captcha()
    {
        $captcha = new Captcha(3);

        do {
            $key = Str::random(6);
        } while (Redis::exists("captcha_key_" . $key));

        Redis::setex("captcha_key_" . $key, 60, $captcha->getCode());

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'captcha_key' => $key,
                'captcha_img' =>  $captcha->base64Img(),
            ],
        );
    }

    public static  function post_hongbao(Request $request, Thread $thread, Post $post)
    {
        $user = $request->user;

        $coin = 0; //红包金额
        $message = ""; //红包回帖信息

        if (Carbon::now() < Carbon::create("2022-2-1 0:0:0")) {
            $coin = 0;
            $message = "本次活动尚未开始，请稍等喔";
        } elseif (Carbon::now() > Carbon::create("2022-2-2 0:0:0")) {
            $coin = 0;
            $message = "本次活动已经结束，你来晚啦";
        } elseif (DB::table("hongbao_record")->where('user_id', $user->id)->lockForUpdate()->exists()) {
            $coin = 0;
            $message = "你已经领取过了，不要贪心喔！";
        } elseif (Carbon::parse($user->created_at) > Carbon::create("2022-1-30 0:0:0")) {
            $coin = 0;
            $message = "你的饼干不符合领取条件";
        } else {
            $rand_num = random_int(1, 10000);
            switch ($rand_num) {
                case $rand_num == 10000: {
                        if (DB::table("hongbao_record")->where('olo', 100000)->exists()) {
                            $coin = 10000;
                            $message = "哇喔，传说！是SSSR！获得了1w个olo，恭喜！ ";
                            break;
                        } else {
                            $coin = 100000;
                            $message = "哇喔，金色传说！屁元从天而降给你施放「屁元的祝福」。获得10w个olo，恭喜！<img src='https://z3.ax1x.com/2021/05/26/2Cntbj.jpg' class='emoji_img'>";
                            break;
                        }
                    }
                case $rand_num >= 9900: {
                        $coin = 10000;
                        $message = "哇喔，传说！是SSSR！获得了1w个olo，恭喜！ ";
                        break;
                    }
                case $rand_num >= 9000: {
                        $coin = 3000;
                        $message = "哇喔！你抽到了SSR！获得了3000个olo，恭喜！ ";
                        break;
                    }
                case $rand_num >= 6000: {
                        $coin = 1000;
                        $message = "你抽到了SR。 获得了1000个olo，恭喜！ ";
                        break;
                    }
                default: {
                        $coin = 500;
                        $message = "你抽到了NR。 获得了500个olo，恭喜！ ";
                        break;
                    }
            }
        }

        $message = "To №" . $post->floor . "：" . $message;

        //执行追加新回复流程
        try {
            DB::beginTransaction();
            $post = new Post;
            $post->setSuffix(intval($request->thread_id / 10000));
            $post->created_binggan = $request->binggan;
            $post->forum_id = $request->forum_id;
            $post->thread_id = $request->thread_id;
            $post->content = $message;
            $post->nickname = '新春红包系统';
            $post->created_by_admin = 2; //0=一般用户 1=管理员发布，2=系统发布
            $post->created_ip = $request->ip();
            $post->random_head = random_int(0, 39);

            $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
            $post->floor = $thread->posts_num;

            $thread->save();
            $post->save();

            if ($coin > 0) {
                $user->coin += $coin;
                $user->save();

                DB::table("hongbao_record")->insert([
                    "thread_id" => $thread->id,
                    "post_id" => $post->id,
                    "user_id" => $user->id,
                    "created_binggan" => $user->binggan,
                    "rand_num" => $rand_num,
                    "olo" => $coin,
                    "created_at" => Carbon::now(),
                ]);
            }
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
        }
    }

    public static  function get_IP_location(string $IP)
    {
        $url = "http://ip-api.com/json/" . $IP . '?fields=17425937';
        // $post_data = array();
        // $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'GET',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                // 'content' => $postdata,
                'timeout' => 15 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return json_decode($result);
    }
}
