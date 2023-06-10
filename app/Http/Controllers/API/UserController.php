<?php

namespace App\Http\Controllers\API;

use App\Common\BattleChara;
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
use Illuminate\Support\Str;
use App\Jobs\ProcessUserActive;
use App\Jobs\ProcessUserCreatedLocation;
use App\Models\AnnoucementMessages;
use App\Models\IncomeStatement;
use App\Models\UserLV;
use App\Models\UserMessages;
use Exception;
use App\Events\NewPostBroadcast;
use App\Exceptions\RequestTooManyException;
use App\Models\UserAchievement;
use App\Models\UserMedal;
use App\Models\UserMedalRecord;
use App\Common\Medals;
use App\Exceptions\UserException;
use App\Models\MyBattleChara;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    //UserLV相关
    const MYEMOJI_MIN = 5000;  //我的表情包初始长度
    const MYEMOJI_MAX = 30000;  //我的表情包最大长度
    const MYEMOJI_INTERVAL = 1000;  //我的表情包每次升级增加长度
    const MYEMOJI_OLO = -20000;  //我的表情包每次升级消费olo

    const TITLE_PINGBICI_MIN = 1000;  //标题屏蔽词初始长度
    const TITLE_PINGBICI_MAX = 4000;  //标题屏蔽词最大长度
    const TITLE_PINGBICI_INTERVAL = 200;  //标题屏蔽词每次升级增加长度
    const TITLE_PINGBICI_OLO = -4000;  //我的表情包每次升级消费olo

    const CONTENT_PINGBICI_MIN = 1000;  //内容屏蔽词每次初始长度
    const CONTENT_PINGBICI_MAX = 4000;  //内容屏蔽词每次最大长度
    const CONTENT_PINGBICI_INTERVAL = 200; //内容屏蔽词每次升级增加长度
    const CONTENT_PINGBICI_OLO = -4000;  //我的表情包每次升级消费olo

    const FJF_PINGBICI_MIN = 1000;  //反精分屏蔽词初始长度
    const FJF_PINGBICI_MAX = 4000;  //反精分屏蔽词最大长度
    const FJF_PINGBICI_INTERVAL = 200;  //反精分屏蔽词每次升级增加长度
    const FJF_PINGBICI_OLO = -4000;  //我的表情包每次升级消费olo

    const MYBATTLECHARA_MIN = 0;  //自定义大乱斗角色初始数量
    const MYBATTLECHARA_MAX = 3;  //自定义大乱斗角色最大数量
    const MYBATTLECHARA = -50000;  //自定义大乱斗角色每次升级消费olo


    /**
     * 生成随机字符串、排除容易混淆的
     */
    private function random_str(int $num)
    {
        $str = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnprstuvwxyz1234567890"; //用于生成随机字符的，排除掉容易混淆的

        $output = '';
        $length = strlen($str);

        for ($i = 0; $i < $num; $i++) {
            // get random char
            $char = $str[rand(0, $length - 1)];
            $output .= $char;
        }

        return $output;
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
        if ($user->tokenCan('forum_admin')) {
            $user->makeVisible('admin');
            $user->append('admin_forums');
        }

        //如果没有存emojis，则返回null（不然前端会报错）
        $my_emoji = $user->MyEmoji;
        if ($my_emoji) {
            $my_emoji_data = $my_emoji->emojis;
        } else {
            $my_emoji_data = null;
        }
        //如果没有升级过饼干user_lv为空，则返回默认值
        $user_lv_data = $user->UserLV;
        if (!$user_lv_data) {
            $user_lv_data = array(
                'title_pingbici' => self::TITLE_PINGBICI_MIN,
                'content_pingbici' => self::CONTENT_PINGBICI_MIN,
                'fjf_pingbici' => self::FJF_PINGBICI_MIN,
                'my_emoji' => self::MYEMOJI_MIN,
                'my_battle_chara' => self::MYBATTLECHARA_MIN,
            );
        }

        //自定义大乱斗角色
        if ($user_lv_data['my_battle_chara'] > 0) {
            $my_battle_chara = MyBattleChara::where('user_id', $user->id)->pluck('name');
        } else {
            $my_battle_chara = [];
        }

        //检查成就（小火锅周年活动）
        $user_medal_record = $user->UserMedalRecord()->firstOrCreate(); //如果记录不存在就追加
        $user_medal_record->check_anniversary();

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '饼干认证成功，欢迎回来',
                'data' => [
                    'binggan' => $user,
                    'pingbici' => $user->pingbici,
                    'my_emoji' => $my_emoji_data,
                    'user_lv' => $user_lv_data,
                    'my_battle_chara' => $my_battle_chara,
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
        $created_UUID_short = substr($created_UUID, 10, 16);
        //并且字符串的开始应为：XiaoHuoGuo
        if (substr($created_UUID, 0, 10) != "XiaoHuoGuo") {
            ProcessUserActive::dispatch(
                [
                    'user_id' => '0',
                    'binggan' => 'none',
                    'active' => '怀疑有人用脚本申请饼干',
                    'content' => sprintf('ip:%s  UUID:%s', $request->ip(), $created_UUID),
                ]
            );
            return response()->json([
                'code' => ResponseCode::USER_REGISTER_FAIL,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_REGISTER_FAIL] . '，是否使用了非正常手段申请饼干？如有疑问请联络：Bombaxceiba@protonmail.com',
                'uuid' => $created_UUID_short,
            ]);
        }

        //确认UUID是否被ban
        if (DB::table('user_register')->where('created_UUID', $created_UUID_short)->value('is_banned')) {
            ProcessUserActive::dispatch(
                [
                    'user_id' => 'none',
                    'active' => '申请饼干但UUID过多而失败',
                    'content' => 'ip:' . $request->ip() . ' UUID:' . $created_UUID_short,
                ]
            );
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
            $binggan = '';
            do {
                $binggan = $this->random_str(9);
            } while (User::where('binggan', $binggan)->exists());
            $user->binggan = $binggan;
            $user->created_ip = $request->ip();
            $user->created_UUID = $created_UUID_short;
            $user->coin = 300;
            $user->save();

            //添加相应的成就进度记录
            $user_medal_record = new UserMedalRecord([
                'user_id' => $user->id,
            ]);
            $user_medal_record->save();

            //记录UUID申请次数
            if (DB::table('user_register')->where('created_UUID', $created_UUID_short)->exists()) {
                DB::table('user_register')->where('created_UUID', $created_UUID_short)->increment('count');
            } else {
                DB::table('user_register')->insert([
                    'created_UUID' => $created_UUID_short
                ]);
            }

            //如果申请次数已经达到5次，则ban
            if (DB::table('user_register')->where('created_UUID', $created_UUID_short)->value('count') >= 5) {
                DB::table('user_register')->where('created_UUID', $created_UUID_short)->update(['is_banned' => 1]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        $token = $user->createToken($binggan, ['normal'])->plainTextToken;
        //用redis记录饼干申请ip。限定7天内只能申请1次。
        Redis::setex('reg_record_' . $request->ip(), 7 * 24 * 3600, 1);

        //用redis记录新饼干，24小时内不能发表回复。
        Redis::setex('new_user_' . $user->binggan, 24 * 3600, 1);

        //对新用户检查公告并拉取
        // $annoucement_ids_all = AnnoucementMessages::where('type', 1) //type = 1是全体公告
        //     ->where('to_new_users', true)   //to_new_users==true则一定可以拉取
        //     ->pluck('id');

        // $annoucement_ids_to_pull = $annoucement_ids_all; //对于新用户来说，必然拉取全部公告
        //在UserMessages插入相应公告
        // if (!emptyArray($annoucement_ids_to_pull)) {
        //     $user->new_msg = true;
        //     $user->save();
        //     //如果有存在需要新用户看的公告，则new_msg设为true
        //     $annoucements = AnnoucementMessages::whereIn('id', $annoucement_ids_to_pull)->get();
        //     foreach ($annoucements as $annoucement) {
        //         UserMessages::insert(
        //             [
        //                 'user_id' => $user->id,
        //                 'annoucement_id' => $annoucement->id,
        //                 'user_msg_group_id' => null,
        //                 'title' => $annoucement->title,
        //                 'sub_title' => $annoucement->sub_title,
        //                 'is_read' => false,
        //                 'is_deleted' => false,
        //                 'created_at' => Carbon::now(),
        //             ]
        //         );
        //     }
        // }

        //记录申请饼干IP所在地
        ProcessUserCreatedLocation::dispatch(
            [
                'IP' => $request->ip(),
                'user_id' => $user->id,
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '领取饼干成功！建议在个人中心设置密码哦。（新饼干24小时内暂时不能发帖）',
                'data' => [
                    'binggan' => $binggan,
                    'token' => $token,
                ],
            ],
            200
        );
    }

    public function create_custom(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'binggan_apply' => 'required|string|alpha_dash|max:16|min:7',
            'password' => 'required|string|alpha_dash|max:20|min:7',
        ]);

        $user_origin = $request->user;

        if (User::where('binggan', $request->binggan_apply)->exists()) {
            return response()->json([
                'code' => ResponseCode::USER_REGISTER_FAIL,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_REGISTER_FAIL] . '，该饼干已经存在了',
            ]);
        }

        try {
            DB::beginTransaction();
            $user = new User;
            $user->binggan = $request->binggan_apply;
            $user->created_ip = $request->ip();
            $user->is_custom = true;
            $user->coin = 300;
            $user->password = hash('sha256', $request->password . config('app.password_salt'));
            $user->save();

            $user_origin->coinChange(
                'normal', //记录类型
                [
                    'olo' => -100000, //定制饼干花费100000
                    'content' => '申请了定制饼干',
                ]
            ); //通过统一接口、记录操作
            $user_origin->save();

            DB::table('user_custom')->insert([
                'user_id' => $user->id,
                'binggan' => $user->binggan,
                'from_binggan' => $user_origin->binggan,
                'created_at' => Carbon::now(),
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        //检查成就
        $user_medal_record = $user_origin->UserMedalRecord()->firstOrCreate();
        $user_medal_record->check_custom_binggan();

        //记录申请饼干IP所在地
        ProcessUserCreatedLocation::dispatch(
            [
                'IP' => $request->ip(),
                'user_id' => $user->id,
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '新建定制饼干成功！',
                'data' => [
                    'binggan' => $request->binggan_apply,
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
            'coin' => 'required|integer|min:1|max:1000000',
            'forum_id' => 'required|integer',
            'thread_id' => 'required|integer',
            'post_id' => 'required|integer',
        ]);

        $user = $request->user;
        $post_target = Post::suffix(intval($request->thread_id / 10000))->find($request->post_id);
        $user_target = User::where('binggan', $post_target->created_binggan)->first();

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

        //判断是否重复打赏
        $redis_key = sprintf('reward_%s_to_%d', $user->binggan, $request->post_id);
        if (Redis::exists($redis_key)) {
            throw new RequestTooManyException;
        } else {
            Redis::setex($redis_key, 2, 1);
        }


        try {
            DB::beginTransaction();
            // $post = new Post;
            // $post->setSuffix(intval($request->thread_id / 10000));
            // $post->created_binggan = $request->binggan;
            // $post->forum_id = $request->forum_id;
            // $post->thread_id = $request->thread_id;
            // $post->content = "<span class='quote_content'>" .
            //     $request->post_floor_message .
            //     '</span><br>我为你打赏了' . $request->coin .
            //     '块奥利奥<br>——' . $request->content;
            // $post->nickname = '奥利奥打赏系统';
            // $post->created_by_admin = 2; //0=一般用户 1=管理员发布，2=系统发布
            // $post->created_ip = $request->ip();
            // $post->random_head = random_int(0, 39);

            // $thread = $post->thread;
            // $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
            // $post->floor = $thread->posts_num;
            // $thread->save();
            // $post->save();

            $post_content = "<span class='quote_content'>" .
                $request->post_floor_message .
                '</span><br>我为你打赏了' . $request->coin .
                '块奥利奥<br>——' . $request->content;

            $post = Post::create([
                'created_binggan' => $request->binggan,
                'forum_id' => $request->forum_id,
                'thread_id' => $request->thread_id,
                'content' => $post_content,
                'nickname' => '奥利奥打赏系统',
                'created_by_admin' => 2,
                'created_IP' => $request->ip(),
            ]);

            $thread = $post->thread;

            //这个和前端*1.07对不上
            // $tax = ceil($request->coin * 0.07); //税率0.07
            // $coin_pay = $request->coin + $tax;

            if (Carbon::now()->between('2022/11/11 00:00:00', '2022/11/12 00:00:00')) {
                //双十一期间免税
                $tax_rate = 1;
            } else {
                $tax_rate = 1.07;
            }
            $coin_pay = ceil($request->coin * $tax_rate);
            // $user->coinConsume($coin_pay);
            $user->coinChange(
                'normal', //记录类型
                [
                    'olo' => -$coin_pay,
                    'content' => '打赏olo   ——留言：' . $request->content,
                    'user_id_target' => $user_target->id,
                    'binggan_target' => $user_target->binggan,
                    'thread_id' => $thread->id,
                    'thread_title' => $thread->title,
                    'post_id' => $post->id,
                    'floor' => $post->floor,
                ]
            ); //通过统一接口、记录操作  

            // $user_target->coin += $request->coin;
            // $user_target->save();
            $user_target->coinChange(
                'normal', //记录类型
                [
                    'olo' => $request->coin,
                    'user_id_target' => $user->id,
                    'binggan_target' => $user->binggan,
                    'content' => '被打赏olo   ——留言：' . $request->content,
                    'thread_id' => $thread->id,
                    'thread_title' => $thread->title,
                    'post_id' => $post->id,
                    'floor' => $post->floor,
                ]
            ); //通过统一接口、记录操作  

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        //广播发帖动作
        // broadcast(new NewPostBroadcast($request->thread_id, $post->id, $post->floor))->toOthers();
        $post->broadcast();

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户打赏了',
                'binggan_target' => $user_target->binggan,
                'content' => $request->coin . '个饼干 ip:' . $request->ip(),
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

    //设定自定义屏蔽词
    public function pingbici_set(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'use_pingbici' => 'required|boolean',
            'title_pingbici' => 'json',
            'content_pingbici' => 'json',
            'fjf_pingbici' => 'json',
        ]);

        $user = $request->user;
        // $user = User::where('binggan', $request->binggan)->first();
        // if (!$user) {
        //     return response()->json(
        //         [
        //             'code' => ResponseCode::USER_NOT_FOUND,
        //             'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
        //         ],
        //     );
        // }

        //检查屏蔽词长度是否符合饼干等级
        $user_lv = $user->UserLV;
        if (!$user_lv) {
            //如果不存在，则输入默认值
            $user_lv = array(
                'title_pingbici' => self::TITLE_PINGBICI_MIN,
                'content_pingbici' => self::CONTENT_PINGBICI_MIN,
                'fjf_pingbici' => self::FJF_PINGBICI_MIN,
                'my_emoji' => self::MYEMOJI_MIN,
            );
        }
        $user_lv_array = array(
            'title_pingbici' => '标题屏蔽词',
            'content_pingbici' => '内容屏蔽词',
            'fjf_pingbici' => '反精分屏蔽词',
        );
        foreach ($user_lv_array as $name => $error_msg) {
            if (mb_strlen($request[$name]) > $user_lv[$name]) {
                return response()->json([
                    'code' => ResponseCode::USER_ERROR,
                    'message' => $error_msg . '长度为' . mb_strlen($request[$name]) . '。已超出了最大限制，可在个人中心升级限制。',
                ]);
            }
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
            $pingbici->fjf_pingbici = $request->fjf_pingbici;
            $user->save();
            $pingbici->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户更新了屏蔽词(新设定)',
                'content' => 'title长度:' . mb_strlen($request->title_pingbici)
                    . 'content长度:' . mb_strlen($request->content_pingbici)
                    . 'fjf长度:' . mb_strlen($request->fjf_pingbici),
            ]
        );

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

    //追加自定义屏蔽词
    public function pingbici_add(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'content_pingbici' => 'required|string',
        ]);

        $user = $request->user;

        $pingbici = $user->Pingbici;
        if ($pingbici) {
            $pingbici_array = json_decode($user->Pingbici->content_pingbici);
            if (in_array($request->content_pingbici, $pingbici_array)) {
                return response()->json([
                    'code' => ResponseCode::USER_ERROR,
                    'message' => '该内容屏蔽词已存在：' . $request->content_pingbici,
                ]);
            }
            array_push($pingbici_array, $request->content_pingbici);
        } else {
            $pingbici = new Pingbici();
            $pingbici_array = [$request->content_pingbici]; //得是个数组
        }

        //检查屏蔽词长度是否符合饼干等级
        $user_lv = $user->UserLV;
        if (!$user_lv) {
            //如果不存在，则输入默认值
            $user_lv = array(
                'title_pingbici' => self::TITLE_PINGBICI_MIN,
                'content_pingbici' => self::CONTENT_PINGBICI_MIN,
                'fjf_pingbici' => self::FJF_PINGBICI_MIN,
                'my_emoji' => self::MYEMOJI_MIN,
            );
        }
        $user_lv_array = array(
            // 'title_pingbici' => '标题屏蔽词',
            'content_pingbici' => '内容屏蔽词',
            // 'fjf_pingbici' => '反精分屏蔽词',
        );
        foreach ($user_lv_array as $name => $error_msg) {
            if (mb_strlen($request[$name]) + mb_strlen($pingbici->$name) > $user_lv[$name]) {
                return response()->json([
                    'code' => ResponseCode::USER_ERROR,
                    'message' => $error_msg . '长度为' . mb_strlen($request[$name]) . '。已超出了最大限制，可在个人中心升级限制。',
                ]);
            }
        }


        try {
            DB::beginTransaction();
            $user->use_pingbici = true;
            $pingbici->user_id = $user->id;
            // $pingbici->title_pingbici = $request->title_pingbici;
            $pingbici->content_pingbici = json_encode($pingbici_array);
            // $pingbici->fjf_pingbici = $request->fjf_pingbici;
            $user->save();
            $pingbici->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户更新了屏蔽词(追加)',
                'content' => '长度:' . mb_strlen($request->content_pingbici),
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '已追加屏蔽词',
                'content_pingbici' => json_encode($pingbici_array),
            ],
        );
    }

    //显示自定义大乱斗角色
    public function my_battle_chara_show(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
        ]);

        $user = $request->user;

        $my_battle_chara = MyBattleChara::where('user_id', $user->id)->get();

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '查询成功！',
                'data' => $my_battle_chara,
            ]
        );
    }

    //设定自定义大乱斗角色
    public function my_battle_chara_set(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'chara_id' => 'required|integer',
            'name' => 'required|string|max:50',
            'heads' => 'required|json|max:1000',
            'messages' => 'required|json|max:1000',
        ]);

        $user = $request->user;

        //检查角色数量是否符合饼干等级
        $user_lv = $user->UserLV['my_battle_chara'];
        if (!$user_lv) {
            //如果不存在，则输入默认值
            $user_lv = self::MYBATTLECHARA_MIN;
        }

        if ($request->chara_id + 1 > $user_lv) { //因为chara_id从0开始，这里要+1
            return response()->json([
                'code' => ResponseCode::USER_ERROR,
                'message' => '自定义大乱斗角色最大为' . $user_lv . '个。已超出了最大限制，可在个人中心升级。',
            ]);
        }

        try {
            DB::beginTransaction();

            $my_battle_chara = MyBattleChara::where(['user_id' => $user->id, 'chara_id' => $request->chara_id])
                ->update(['name' => $request->name, 'heads' => $request->heads, 'messages' => $request->messages]);
            // $my_battle_chara->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => sprintf('用户更新了大乱斗角色(id:%d)', $request->chara_id),
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => sprintf('已更新我的大乱斗角色：%s', $request->name),
            ],
        );
    }

    //追加我的表情包
    public function my_emoji_add(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'my_emoji' => 'required|string',
        ]);

        $user = $request->user;

        if ($user->MyEmoji) {
            $my_emoji = $user->MyEmoji;
            $my_emoji_array = json_decode($my_emoji->emojis);
            array_push($my_emoji_array, $request->my_emoji);
        } else {
            $my_emoji = new MyEmoji();
            $my_emoji_array = [$request->my_emoji]; //得是个数组
        }

        //检查我的表情包长度是否符合饼干等级
        $user_lv = $user->UserLV;
        if (!$user_lv) {
            //如果不存在，则输入默认值
            $user_lv = array(
                'title_pingbici' => self::TITLE_PINGBICI_MIN,
                'content_pingbici' => self::CONTENT_PINGBICI_MIN,
                'fjf_pingbici' => self::FJF_PINGBICI_MIN,
                'my_emoji' => self::MYEMOJI_MIN,
            );
        }
        $user_lv_array = array(
            'my_emoji' => '我的表情包',
        );
        foreach ($user_lv_array as $name => $error_msg) {
            if (mb_strlen($request[$name]) + mb_strlen($my_emoji->emojis) > $user_lv[$name]) {
                return response()->json([
                    'code' => ResponseCode::USER_ERROR,
                    'message' => $error_msg . '长度为' . (mb_strlen($request[$name]) + mb_strlen($my_emoji->emojis)) . '。已超出了最大限制，可在个人中心升级限制。',
                ]);
            }
        }

        try {
            DB::beginTransaction();
            $my_emoji->user_id = $user->id;
            $my_emoji->emojis = json_encode($my_emoji_array);
            $my_emoji->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户更新了表情包(追加)',
                'content' => '长度:' . mb_strlen($request->my_emoji),
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '已设定我的表情包',
                'data' => [
                    'my_emoji' => json_encode($my_emoji_array),
                    'len' => mb_strlen($request['my_emoji']),
                ]
            ],
        );
    }

    //设定我的表情包
    public function my_emoji_set(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'my_emoji' => 'json',
        ]);

        $user = $request->user;

        //检查我的表情包长度是否符合饼干等级
        $user_lv = $user->UserLV;
        if (!$user_lv) {
            //如果不存在，则输入默认值
            $user_lv = array(
                'title_pingbici' => self::TITLE_PINGBICI_MIN,
                'content_pingbici' => self::CONTENT_PINGBICI_MIN,
                'fjf_pingbici' => self::FJF_PINGBICI_MIN,
                'my_emoji' => self::MYEMOJI_MIN,
            );
        }
        $user_lv_array = array(
            'my_emoji' => '我的表情包',
        );
        foreach ($user_lv_array as $name => $error_msg) {
            if (mb_strlen($request[$name]) > $user_lv[$name]) {
                return response()->json([
                    'code' => ResponseCode::USER_ERROR,
                    'message' => $error_msg . '长度为' . mb_strlen($request[$name]) . '。已超出了最大限制，可在个人中心升级限制。',
                ]);
            }
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
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户更新了表情包(更新)',
                'content' => '长度:' . mb_strlen($request->my_emoji),
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '已设定我的表情包',
                'data' => [
                    'my_emoji' => $my_emoji->emojis,
                    'len' => mb_strlen($request['my_emoji']),
                ]
            ],
        );
    }

    //解除灌水限制
    public function water_unlock(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'captcha_key' => 'required|string',
            'captcha_code' => 'required|string',
        ]);

        if (Redis::exists("captcha_key_" . $request->captcha_key)) {
            $captcha_code = Redis::get("captcha_key_" . $request->captcha_key);
            if ($captcha_code == strtolower($request->captcha_code)) {
                Redis::del('new_post_record_IP_' . $request->ip());
                return response()->json([
                    'code' => ResponseCode::SUCCESS,
                    'message' => '已解除限制。',
                ]);
            } else {
                ProcessUserActive::dispatch(
                    [
                        'binggan' => $request->user->binggan,
                        'user_id' => $request->user->id,
                        'active' => '用户输入验证码错误',
                        'content' => 'code:' . $captcha_code . ' input:' . $request->captcha_code,
                    ]
                );
                return response()->json([
                    'code' => ResponseCode::CAPTCHA_WRONG,
                    'message' => ResponseCode::$codeMap[ResponseCode::CAPTCHA_WRONG],
                ]);
            }
        } else {
            return response()->json([
                'code' => ResponseCode::CAPTCHA_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::CAPTCHA_NOT_FOUND],
            ]);
        }
    }

    //查询收益表
    public function income_show(Request $request)
    {
        $request->validate([
            'income_date' => 'required|date|after_or_equal:2022-01-01',
            'mode' => 'string',
        ]);

        $user = $request->user;
        $mode = $request->mode == null ? 'list_day' : $request->mode; //默认是day

        switch ($mode) {
            case 'list_day': {
                    //获得查询当天的全部数据
                    $income_data = IncomeStatement::incomeData($user->id, $request->income_date); //更好的分页sql语句
                    return response()->json(
                        [
                            'code' => ResponseCode::SUCCESS,
                            'message' => '返回收益表',
                            'data' => array(
                                "income_data" => $income_data,
                            )
                        ]
                    );
                }
            case 'sum_month&year': {
                    //获得查询当年和当月的合计
                    $date = Carbon::parse($request->income_date);
                    $sum_year = IncomeStatement::suffix($date->year)->where('user_id', $user->id)->sum('olo');

                    $from_date = $date->copy()->firstOfMonth()->toDateString();
                    $to_date = $date->copy()->addMonthNoOverflow()->firstOfMonth()->toDateString();

                    $sum_month = IncomeStatement::suffix($date->year)->where('user_id', $user->id)->whereBetween('created_at', [$from_date, $to_date])->sum('olo');
                    return response()->json(
                        [
                            'code' => ResponseCode::SUCCESS,
                            'message' => '返回收益表',
                            'data' => array(
                                "sum_year" => $sum_year,
                                "sum_month" => $sum_month,
                            )
                        ]
                    );
                }
            default: {
                    return response()->json(
                        [
                            'code' => ResponseCode::FILED_ERROR,
                            'message' => '请求参数mode错误',
                        ]
                    );
                }
        }
    }

    //饼干升级
    public function user_lv_up(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'mode' => 'required|string',
        ]);

        $user = $request->user;

        try {
            DB::beginTransaction();
            if ($user->UserLV) {
                $user_lv = $user->UserLV;
            } else {
                $user_lv = new UserLV();
                $user_lv->user_id = $user->id;
                $user_lv->title_pingbici = self::TITLE_PINGBICI_MIN;
                $user_lv->content_pingbici = self::CONTENT_PINGBICI_MIN;
                $user_lv->fjf_pingbici = self::FJF_PINGBICI_MIN;
                $user_lv->my_emoji = self::MYEMOJI_MIN;
                $user_lv->my_battle_chara = self::MYBATTLECHARA_MIN;
            }
            switch ($request->mode) {
                case 'title_pingbici':
                    $user_lv->title_pingbici += self::TITLE_PINGBICI_INTERVAL;
                    if ($user_lv->title_pingbici > self::TITLE_PINGBICI_MAX) {
                        throw new UserException('标题屏蔽词已升到满级了');
                    }
                    $user->coinChange(
                        'normal', //记录类型
                        [
                            'olo' => self::TITLE_PINGBICI_OLO,
                            'content' => '升级饼干（标题屏蔽词）',
                        ]
                    ); //扣除奥利奥（通过统一接口、记录操作）
                    break;
                case 'content_pingbici':
                    $user_lv->content_pingbici += self::CONTENT_PINGBICI_INTERVAL;
                    if ($user_lv->content_pingbici > self::CONTENT_PINGBICI_MAX) {
                        throw new UserException('内容屏蔽词已升到满级了');
                    }
                    $user->coinChange(
                        'normal', //记录类型
                        [
                            'olo' => self::CONTENT_PINGBICI_OLO,
                            'content' => '升级饼干（内容屏蔽词）',
                        ]
                    ); //扣除奥利奥（通过统一接口、记录操作）
                    break;
                case 'fjf_pingbici':
                    $user_lv->fjf_pingbici += self::FJF_PINGBICI_INTERVAL;
                    if ($user_lv->fjf_pingbici > self::FJF_PINGBICI_MAX) {
                        throw new UserException('内容屏蔽词已升到满级了');
                    }
                    $user->coinChange(
                        'normal', //记录类型
                        [
                            'olo' => self::FJF_PINGBICI_OLO,
                            'content' => '升级饼干（反精分屏蔽词）',
                        ]
                    ); //扣除奥利奥（通过统一接口、记录操作）
                    break;
                case 'my_emoji':
                    $user_lv->my_emoji += self::MYEMOJI_INTERVAL;
                    if ($user_lv->my_emoji > self::MYEMOJI_MAX) {
                        throw new UserException('我的表情包已升到满级了');
                    }
                    $user->coinChange(
                        'normal', //记录类型
                        [
                            'olo' => self::MYEMOJI_OLO,
                            'content' => '升级饼干（我的表情包）',
                        ]
                    ); //扣除奥利奥（通过统一接口、记录操作）
                    break;
                case 'my_battle_chara':
                    $user_lv->my_battle_chara += 1;
                    if ($user_lv->my_battle_chara > self::MYBATTLECHARA_MAX) {
                        throw new UserException('自定义大乱斗角色数量已经达到最大值了');
                    }

                    $default_chara = new BattleChara(8); //默认角色小豆泥(id=8)

                    try {
                        DB::beginTransaction();
                        Log::debug('user_lv', [$user_lv]);
                        Log::debug('$user_lv->my_battle_chara - 1', [$user_lv->my_battle_chara - 1]);
                        MyBattleChara::create(
                            [
                                'user_id' => $user->id,
                                'chara_id' => $user_lv->my_battle_chara - 1, //chara_id从0开始，这里要-1
                                'name' => $default_chara->CharaName(),
                                'heads' => $default_chara->CharaHeadsAll(),
                                'messages' => $default_chara->CharaAttackMessagesAll(),
                            ]
                        );

                        $user->coinChange(
                            'normal', //记录类型
                            [
                                'olo' => self::MYBATTLECHARA,
                                'content' => '升级饼干（自定义大乱斗角色+1）',
                            ]
                        ); //扣除奥利奥（通过统一接口、记录操作）
                        DB::commit();
                    } catch (Exception $e) {
                        DB::rollback();
                        throw $e;
                    }
                    break;
                default:
                    throw new UserException('升级出错');
            }
            $user_lv->save();

            $user->user_lv += 1;
            $user->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        //检查成就
        UserMedalRecord::Check_user_lv($user);

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '成功升级饼干！',
            ]
        );
    }

    //查询站内消息列表
    public function show_messages_index(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'page' => 'integer|nullable',
            'per_page' => 'integer|nullable|max:100|min:1',
        ]);
        $user = $request->user; //从sanctum的token获得饼干

        //如果有消息标记，则检查公告并拉取
        if ($user->new_msg == true) {
            $annoucement_ids_pulled = UserMessages::where('user_id', $user->id)->whereNot('annoucement_id', null)->pluck('announcement_id');
            $annoucement_ids_all = AnnoucementMessages::where('type', 1) //type = 1是全体公告
                ->where('to_new_users', true)   //to_new_users==true则一定可以拉取
                ->orWhere(function ($query) use ($user) { //又或者to_new_users==false，但created_at在user的created_at之后
                    $query->where('to_new_users', false)
                        ->where('created_at', '>', $user->created_at);
                })
                ->pluck('id');
            $annoucement_ids_to_pull = array_diff_key($annoucement_ids_all, $annoucement_ids_pulled);

            //在UserMessages插入相应公告
            if (!emptyArray($annoucement_ids_to_pull)) {
                $annoucements = AnnoucementMessages::whereIn('id', $annoucement_ids_to_pull)->get();
                foreach ($annoucements as $annoucement) {
                    UserMessages::insert(
                        [
                            'user_id' => $user->id,
                            'annoucement_id' => $annoucement->id,
                            'user_msg_group_id' => null,
                            'title' => $annoucement->title,
                            'sub_title' => $annoucement->sub_title,
                            'is_read' => false,
                            'is_deleted' => false,
                            'created_at' => Carbon::now(),
                        ]
                    );
                }
            }
        }

        $per_page = $request->per_page == null ? 20 : $request->per_page; //默认20个每页
        $messages_index = UserMessages::where('user_id', $user->id)->paginate($per_page);

        $user->new_msg = false;
        $user->save();

        // UserMessages::where('user_id', $user->id)->where('is_read', false)->update(['is_read' => true]);//将
        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '返回站内消息列表',
                'data' => array(
                    "user_messages" => $messages_index,
                )
            ]
        );
    }


    public function show_messages_content(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'type' => 'required|stirng',
            'announcement_id' => 'required_without:user_msg_group_id|integer',
            'user_msg_group_id' => 'required_without:announcement_id|integer',
        ]);
        $user = $request->user;

        switch ($request->type) {
            case 'annoucement': {
                    $result = AnnoucementMessages::find($request->annoucement_id);
                    if (!$result) {
                        return response()->json([
                            'code' => ResponseCode::ANNOUCEMENT_NOT_FOUND,
                            'message' => ResponseCode::$codeMap[ResponseCode::ANNOUCEMENT_NOT_FOUND],
                        ]);
                    }
                    UserMessages::where('user_id', $user->id)
                        ->where('annoucement_id', $request->annoucement_id)
                        ->update(['is_read' => true]);
                    break;
                }
            case 'user_msg_group': {
                    //用户私信功能还没做
                    break;
                }
            default: {
                    return response()->json([
                        'code' => 422,
                        'message' => '请求参数有误',
                    ]);
                }
        }

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '返回站内消息详细内容',
                'data' => $result,
            ]
        );
    }

    public function show_medals(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
        ]);

        UserMedal::where('user_id', $request->user->id)->where('is_read', 0)->update(['is_read' => 1]);
        $user_medals = $request->user->UserMedal()->pluck('medal_id')->toArray();
        // $user_medals->where('is_read', 0)->update(['is_read' => 1]);
        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '已获取成就数据',
                'data' => $user_medals,
            ]
        );
    }

    public function show_medal_progress(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'medal_id' => 'required|integer',
        ]);

        $user = $request->user;
        if (!$user) {
            return response()->json([
                'code' => ResponseCode::USER_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
            ]);
        }
        $varname = Medals::DATA[$request->medal_id]['varname'];

        $threshold = Medals::DATA[$request->medal_id]['threshold'];

        $this_medal = $user->UserMedal()->where('medal_id', $request->medal_id)->first();
        if ($this_medal) {
            $medal_created_at = $this_medal->created_at;
        } else {
            $medal_created_at = null;
        }

        if ($varname == 'olo') {
            $progress = $user->coin;
        } elseif ($varname == 'user_lv') {
            $progress = $user->user_lv;
        } elseif ($varname != null) {
            $progress_arr = $user->UserMedalRecord()->pluck($varname)->toArray();
            Log::debug("progress_arr", [$progress_arr]);
            if ($progress_arr) {
                $progress = $progress_arr[0];
            } else {
                $progress = 0;
            }
        } else {
            $progress = null;
        }

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '已获取成就进度',
                'data' => [
                    'medal_id' => $request->medal_id,
                    'threshold' => $threshold,
                    'progress' => $progress,
                    'medal_created_at' => $medal_created_at,
                ],
            ]
        );
    }
}
