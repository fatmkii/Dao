<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Common\ResponseCode;
use App\Exceptions\CoinException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\User;
use App\Models\Post;
use App\Models\Pingbici;
use App\Models\MyEmoji;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Jobs\ProcessUserActive;

class UserController extends Controller
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
    public function show(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
        ]);

        $user = $request->user(); //从sanctum的token获得饼干
        $user->last_login = Carbon::now();
        $user->save();

        if ($user->is_banned) {
            return response()->json(
                [
                    'code' => ResponseCode::USER_BANNED,
                    'message' => ResponseCode::$codeMap[ResponseCode::USER_BANNED],
                    'data' => [
                        'binggan' => $user->binggan,
                    ],
                ],
                401
            );
        }

        //用redis缓存所有emoji
        // $emojis = Cache::remember('emoji_cache',  24 * 3600, function () {
        //     return DB::table('emoji')->get();
        // });

        //确认饼干是否一致
        if ($user->binggan != $request->get('binggan')) {
            return response()->json(
                [
                    'code' => ResponseCode::CANNOTLOGIN,
                    'message' => ResponseCode::$codeMap[ResponseCode::CANNOTLOGIN],
                ],
                401
            );
        }

        //如果持有管理员token，使admin属性暴露，追加admin_forums属性
        if ($user->tokenCan('admin')) {
            $user->makeVisible('admin');
            $user->append('admin_forums');
        }

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '饼干认证成功，欢迎回来',
                'data' => [
                    'binggan' => $user,
                    'pingbici' => $user->pingbici,
                    'my_emoji' => $user->MyEmoji,
                    // 'emojis' => $emojis,
                ],
            ],
        );
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

    public function create(Request $request)
    {
        $request->validate([
            'register_key' => 'required|string',
        ]);

        if (!config('app.new_binggan')) {
            return response()->json([
                'code' => ResponseCode::USER_NEW_CLOSED,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_NEW_CLOSED],
            ]);
        }

        if (Redis::exists('reg_record_' . $request->ip())) {
            $limted_day = intval(Redis::TTL('reg_record_' . $request->ip()) / 86400) + 1;
            return response()->json([
                'code' => ResponseCode::USER_REGISTER_FAIL,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_REGISTER_FAIL] . '，你只能在'
                    . $limted_day . '天后再领取饼干。',
            ]);
        }

        //经过AES加密的Canvas指纹，作为UUID判断是否重复申请饼干
        //密钥：XiaoHuoGuoCpttmm
        $key = "XiaoHuoGuoCpttmm";
        $iv = 'abcdef0123456789';
        $options = OPENSSL_ZERO_PADDING;
        $created_UUID = openssl_decrypt($request->register_key, 'aes-128-cbc', $key, $options, $iv);
        $created_UUID_short = substr($created_UUID, 10, 8);
        //并且字符串的开始应为：XiaoHuoGuo
        if (substr($created_UUID, 0, 10) != "XiaoHuoGuo") {
            return response()->json([
                'code' => ResponseCode::USER_REGISTER_FAIL,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_REGISTER_FAIL] . '，是否使用了非正常手段申请饼干？如有疑问请联络：Bombaxceiba@protonmail.com',
                'uuid' => $created_UUID_short,
            ]);
        }

        //确认UUID是否被ban
        if (DB::table('user_register')->where('created_UUID', $created_UUID_short)->value('is_banned')) {
            return response()->json([
                'code' => ResponseCode::USER_REGISTER_FAIL,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_REGISTER_FAIL] .
                    '，是否申请了过多饼干？如有疑问请联络：Bombaxceiba@protonmail.com，' .
                    '附带：' . $created_UUID_short,
                'uuid' => $created_UUID_short,
            ]);
        }

        try {
            DB::beginTransaction();
            $user = new User;
            do {
                $binggan = Str::random(9);
            } while (!empty(User::where('binggan', $binggan)->first));
            $user->binggan = $binggan;
            $user->created_ip = $request->ip();
            $user->created_UUID = $created_UUID_short;
            $user->coin = 1500;
            $user->save();

            //记录UUID申请次数
            if (DB::table('user_register')->where('created_UUID', $created_UUID_short)->exists()) {
                DB::table('user_register')->where('created_UUID', $created_UUID_short)->increment('count');
            } else {
                DB::table('user_register')->insert([
                    'created_UUID' => $created_UUID_short
                ]);
            }

            //如果申请次数已经达到10次，则ban
            if (DB::table('user_register')->where('created_UUID', $created_UUID_short)->value('count') >= 10) {
                DB::table('user_register')->where('created_UUID', $created_UUID_short)->update(['is_banned' => 1]);
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试',
            ]);
        }
        $token = $user->createToken($binggan, ['normal'])->plainTextToken;
        //用redis记录饼干申请ip。限定7天内只能申请1次。
        Redis::setex('reg_record_' . $request->ip(), 7 * 24 * 3600, 1);

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '创建饼干成功！',
                'data' => [
                    'binggan' => $binggan,
                    'token' => $token,
                ],
            ],
            200
        );
    }

    public function check_reg_record(Request $request)
    {
        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '申请饼干记录TTL',
                'data' => [
                    'reg_record_TTL' => Redis::TTL('reg_record_' . $request->ip()),
                ],
            ],
        );
    }

    public function reward(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'post_floor_message' => 'required|string',
            'coin' => 'required|integer',
            'forum_id' => 'required|integer',
            'thread_id' => 'required|integer',
            'post_id' => 'required|integer',
        ]);

        $user = $request->user;
        // $user = User::where('binggan', $request->binggan)->first();
        $post_target = Post::suffix(intval($request->thread_id / 10000))->find($request->post_id);
        $user_target = User::where('binggan', $post_target->created_binggan)->first();

        //判断用户饼干是否存在
        // if (!$user) {
        //     return response()->json([
        //         'code' => ResponseCode::USER_NOT_FOUND,
        //         'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
        //     ]);
        // }
        //判断对象用户饼干是否存在
        if (!$user_target) {
            return response()->json([
                'code' => ResponseCode::USER_NOT_FOUND,
                'message' => '打赏对象不存在',
            ]);
        }
        //判断用户饼干和对象饼干是否一致
        if ($user_target->binggan == $request->binggan) {
            return response()->json([
                'code' => ResponseCode::DEFAULT,
                'message' => '给自己打赏是想给税收做贡献吗？',
            ]);
        }

        try {
            DB::beginTransaction();
            $tax = ceil($request->coin * 0.07); //税率0.07
            $coin_pay = $request->coin + $tax;
            $user->coin -= $coin_pay;
            $user_target->coin += $request->coin;
            $user->save();
            $user_target->save();
            if ($user->coin < 0) {
                throw new CoinException();
            }
            $post = new Post;
            $post->setSuffix(intval($request->thread_id / 10000));
            $post->created_binggan = $request->binggan;
            $post->forum_id = $request->forum_id;
            $post->thread_id = $request->thread_id;
            $post->content = "<span class='quote_content'>" .
                $request->post_floor_message .
                '</span><br>我为你打赏了' . $request->coin .
                '块奥利奥<br>——' . $request->content;
            $post->nickname = '奥利奥打赏系统';
            $post->created_by_admin = 2; //0=一般用户 1=管理员发布，2=系统发布
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
                'active' => '用户打赏了',
                'binggan_target' => $user_target->binggan,
                'content' => $request->coin . '个饼干',
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '打赏成功！对方获得' . $request->coin . '个奥利奥，你减少了' . $coin_pay . '个奥利奥',
                'data' => [
                    'binggan' => $request->binggan,
                    'binggan_target' => $request->binggan_target,
                    'coin' => $request->coin
                ]
            ],
        );
    }

    public function pingbici_set(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'use_pingbici' => 'required|boolean',
            'title_pingbici' => 'json|max:1000',
            'content_pingbici' => 'json|max:1000',
        ]);

        $user = User::where('binggan', $request->binggan)->first();
        if (!$user) {
            return response()->json(
                [
                    'code' => ResponseCode::USER_NOT_FOUND,
                    'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
                ],
            );
        }

        if ($user->pingbici) {
            $pingbici = $user->pingbici;
        } else {
            $pingbici = new Pingbici();
        }
        try {
            DB::beginTransaction();
            $user->use_pingbici = $request->use_pingbici;
            $pingbici->user_id = $user->id;
            $pingbici->title_pingbici = $request->title_pingbici;
            $pingbici->content_pingbici = $request->content_pingbici;
            $user->save();
            $pingbici->save();
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试',
            ]);
        }

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '已设定屏蔽词',
                'data' => [
                    'pingbici' => $pingbici,
                ]
            ],
        );
    }

    public function my_emoji_set(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'my_emoji' => 'json|max:5000',
        ]);

        $user = User::where('binggan', $request->binggan)->first();
        if (!$user) {
            return response()->json(
                [
                    'code' => ResponseCode::USER_NOT_FOUND,
                    'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
                ],
            );
        }

        if ($user->MyEmoji) {
            $my_emoji = $user->MyEmoji;
        } else {
            $my_emoji = new MyEmoji();
        }
        try {
            DB::beginTransaction();
            $my_emoji->user_id = $user->id;
            $my_emoji->emojis = $request->my_emoji;
            $my_emoji->save();
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试',
            ]);
        }

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '已设定我的表情包',
                'data' => [
                    'my_emoji' => $my_emoji,
                ]
            ],
        );
    }
}
