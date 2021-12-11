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
}
