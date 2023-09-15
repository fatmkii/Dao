<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use App\Exceptions\CoinException;
use App\Common\ResponseCode;
use App\Models\Pingbici;
use App\Models\MyEmoji;
use App\Models\UserLV;
use App\Models\Admin;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use App\Jobs\ProcessUserActive;
use App\Models\IncomeStatement;
use App\Jobs\ProcessIncomeStatement;
use App\Models\UserMessages;
use App\Models\UserMedalRecord;
use App\Models\UserMedal;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'binggan',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'binggan',
        'admin',
        'password',
        'created_location',
        'created_UUID',
        'is_custom',
        'last_login',
        'locked_count',
        'created_ip',
        'created_at',
        'updated_at',
        'is_banned',
        'locked_until',
        'AdminPermissions',
        'pingbici',
        'MyEmoji',
        'UserLV',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'binggan_verified_at' => 'datetime',
        'last_login' => 'datetime',
    ];

    protected $appends = [
        'locked_TTL',
    ];

    //防灌水相关
    const NEW_THREAD_INTERVAL = 300; //发新主题频率
    const NEW_POST_NUMBER = 10; //饼干回帖次数（也就是60秒10次）
    const NEW_POST_INTERVAL = 60; //饼干回帖频率（含大乱斗）
    const NEW_POST_NUMBER_IP = 100; //IP回帖次数（也就是1小时100次）
    const NEW_POST_NUMBER_IP2 = 5; //IP回帖不看帖次数（第3类）
    const NEW_POST_NUMBER_IP3 = 70; //IP回帖方差（第4类）
    const NEW_POST_NUMBER_IP4 = 20; //IP回帖前端的特征码（第5类），每20贴检查1次
    const NEW_POST_INTERVAL_IP = 3600; //IP回帖频率（含大乱斗）

    protected static function booted()
    {
        // static::retrieved(function ($user) {
        //     if ($user->admin != 0) {
        //         $user->append('admin_forums');
        //     }
        // });
    }

    //计算平均值和方差
    private function get_avg_var(array $array)
    {
        $sum = 0;
        $count = count($array);
        foreach ($array as $num) {
            $sum += $num;
        }
        $avg = $sum / $count;

        $square = 0;
        foreach ($array as $num) {
            $square += pow($num - $avg, 2);
        }
        $variance = $square / $count;

        return [$avg, $variance];
    }

    //发帖、回帖频率检查
    public function waterCheck(string $action, string $ip, int $thread_id = null, Request $request = null)
    {
        switch ($action) {
            case 'new_post': {

                    $records =
                        [
                            // 'new_post_record' => 'new_post_record_' . $this->binggan, //记录饼干的redis
                            'new_post_record' => 'new_post_record_' . $ip, //1分钟10贴的记录（从饼干改为IP）
                            'new_post_record_IP' => 'new_post_record_IP_' . $ip, //不输入验证码就不消除的
                            'new_post_record_IP2' => 'new_post_record_IP2_' . $ip, //每次看帖行为就消除的
                        ];


                    foreach ($records as $name => $redis_key) {
                        if (Redis::TTL($redis_key) == -1) {
                            //偶然会出现TTL失效，此时redis的key
                            Redis::del($redis_key);
                            Log::channel('my_log')->error('new_post_record expired failed', ['key' => $redis_key]);
                        };
                        //批量查询和赋值
                        $$name = Redis::GET($redis_key);
                    }

                    // $new_post_record = Redis::GET('new_post_record_' . $this->binggan);
                    // $new_post_record_IP = Redis::GET('new_post_record_IP_' . $ip);
                    // $new_post_record_IP2 = Redis::GET('new_post_record_IP2_' . $ip);

                    //第1类检查：饼干记录，1分钟最多10贴
                    if ($new_post_record >= self::NEW_POST_NUMBER && $this->admin < 10) {
                        return response()->json([
                            'code' => ResponseCode::POST_TOO_MANY,
                            'message' => ResponseCode::$codeMap[ResponseCode::POST_TOO_MANY] . '为防止刷屏，每1分钟最多回帖10次（含大乱斗）',
                        ]);
                    }
                    //第2类检查：IP记录，3600秒内100次弹出验证码
                    if ($new_post_record_IP >= self::NEW_POST_NUMBER_IP && $this->admin < 10) {

                        ProcessUserActive::dispatch(
                            [
                                'binggan' => $this->binggan,
                                'user_id' => $this->id,
                                'thread_id' => $thread_id,
                                'active' => '用户触发了机器人刷帖警报',
                                'content' => 'ip:' . $ip . ' record:' . $new_post_record_IP,
                            ]
                        );

                        return response()->json([
                            'code' => ResponseCode::POST_TOO_MANY_MAYBE_ROBOT,
                            'message' => ResponseCode::$codeMap[ResponseCode::POST_TOO_MANY_MAYBE_ROBOT],
                        ]);
                    }

                    //第3类检查：IP记录，只回帖不看帖（防止直接操作API的脚本）
                    if ($new_post_record_IP2 >= self::NEW_POST_NUMBER_IP2 && $new_post_record_IP2 % 5 == 0 && $this->admin < 10) {
                        ProcessUserActive::dispatch(
                            [
                                'binggan' => $this->binggan,
                                'user_id' => $this->id,
                                'thread_id' => $thread_id,
                                'active' => '怀疑用户用脚本刷帖(回帖不看帖)',
                                'content' => 'ip:' . $ip . ' record:' . $new_post_record_IP2,
                            ]
                        );
                    }

                    //第4类检查：IP记录，回顾该用户前20个帖子的发帖间隔方差
                    if ($new_post_record_IP == self::NEW_POST_NUMBER_IP3 && $this->admin < 10) {
                        $posts_time = Post::suffix(intval($thread_id / 10000))
                            ->where('created_binggan', $this->binggan)
                            ->orderBy('id', 'desc')->limit(21)->pluck('created_at')->toArray();

                        $posts_time_d = []; //发帖时间之差
                        for ($i = 0; $i < count($posts_time) - 1; ++$i) {
                            array_push($posts_time_d, $posts_time[$i]->timestamp - $posts_time[$i + 1]->timestamp);
                        }
                        if (count($posts_time_d) == 0) {
                            Log::channel('my_log')->warning('new_post check failed', ['array' => $posts_time_d, 'binggan' => $this->binggan]);
                            break;
                        }

                        list($avg, $variance) = $this->get_avg_var($posts_time_d); //平均值和方差

                        if ($variance < 5) {
                            ProcessUserActive::dispatch(
                                [
                                    'binggan' => $this->binggan,
                                    'user_id' => $this->id,
                                    'thread_id' => $thread_id,
                                    'active' => '怀疑用户用脚本刷帖(方差一致)',
                                    'content' => sprintf("ip:%s avg:%.1f  var:%.1f", $ip, $avg, $variance)
                                ]
                            );
                        }
                    }
                    //第5类检查：
                    if ($new_post_record_IP % self::NEW_POST_NUMBER_IP4 == 0 && $this->admin < 10) {
                        //确认是否脚本机器人发帖（JS脚本类型）
                        $key_1 = md5($request->thread_id . $request->binggan . $request->timestamp . "true"); //浏览器的isTrusted为true时候;
                        if ($request->new_post_key != $key_1) {
                            $key_2 = md5($request->thread_id . $request->binggan . $request->timestamp . "false"); //浏览器的isTrusted为false时候;
                            if ($request->new_post_key == $key_2) {
                                ProcessUserActive::dispatch(
                                    [
                                        'binggan' => $this->binggan,
                                        'user_id' => $this->id,
                                        'active' => '怀疑用户用脚本刷帖(JS脚本类型)',
                                        'thread_id' => $request->thread_id,
                                        'content' => 'ip:' . $request->ip(),
                                    ]
                                );
                                //暂时不返回错误，钓鱼
                                // return response()->json([
                                //     'code' => ResponseCode::POST_ROBOT,
                                //     'message' => ResponseCode::$codeMap[ResponseCode::POST_ROBOT],
                                // ]);
                            } else {
                                ProcessUserActive::dispatch(
                                    [
                                        'binggan' => $this->binggan,
                                        'user_id' => $this->id,
                                        'active' => '怀疑用户用脚本刷帖(key不正确)',
                                        'thread_id' => $request->thread_id,
                                        'content' => 'ip:' . $request->ip(),
                                    ]
                                );
                            }
                        }
                    }

                    break;
                }
            case 'new_thread': {
                    if (Redis::exists('new_thread_record_' . $this->binggan) &&  $this->admin == 0) {
                        $limted_minutes = ceil(Redis::TTL('new_thread_record_' . $this->binggan) / 60);
                        return response()->json([
                            'code' => ResponseCode::THREAD_TOO_MANY,
                            'message' => ResponseCode::$codeMap[ResponseCode::THREAD_TOO_MANY] . '，你只能在'
                                . $limted_minutes . '分钟后再发新主题。',
                        ]);
                    }
                    break;
                }
        }
        return 'ok';
    }

    //发帖、回帖频率写入Redis
    public function waterRecord(string $action, string $ip)
    {
        switch ($action) {
            case 'new_post': {
                    if (Redis::exists('new_post_record_' . $ip)) {
                        Redis::incr('new_post_record_' . $ip);
                    } else {
                        Redis::setex('new_post_record_' . $ip,  self::NEW_POST_INTERVAL, 1);
                    }
                    if (Redis::exists('new_post_record_IP_' . $ip)) {
                        Redis::incr('new_post_record_IP_' . $ip);
                    } else {
                        Redis::setex('new_post_record_IP_' . $ip,  self::NEW_POST_INTERVAL_IP, 1);
                    }
                    if (Redis::exists('new_post_record_IP2_' . $ip)) {
                        Redis::incr('new_post_record_IP2_' . $ip);
                    } else {
                        Redis::setex('new_post_record_IP2_' . $ip,  self::NEW_POST_INTERVAL_IP, 1);
                    }
                    break;
                }
            case 'new_thread': {
                    Redis::setex('new_thread_record_' . $this->binggan, self::NEW_THREAD_INTERVAL, 1);
                    break;
                }
        }
    }

    //有正常看帖行为则清除redis灌水检查记录
    public function waterClear(string $action, string $ip)
    {
        switch ($action) {
            case 'view_post': {
                    Redis::del('new_post_record_IP2_' . $ip);
                    break;
                }
        }
    }

    //消费奥利奥并检查是否足够（用于不留下income_statement的操作）
    public function coinConsume(int $coin)
    {
        if ($this->coin < $coin) {
            throw new CoinException();
        }
        // $this->coin -= $coin;
        $this->increment('coin', -$coin);
        $this->save();

        //检查olo是否清零的成就
        UserMedalRecord::check_olo_0($this);
    }

    //统一的奥利奥变更接口，并且留下income_statement记录
    public function coinChange(string $action = "normal", array $income_statement, bool $ignore_olo_0 = false)
    {
        //检查olo是否足够
        if ($income_statement['olo'] < 0 && $this->coin < -$income_statement['olo']) {
            if ($ignore_olo_0 == false) {
                //如果olo不足，则报错
                throw new CoinException();
            } else {
                //假如忽略olo低于0的情况（用于罚款等情况），直接把剩余olo扣除到0为止
                $income_statement['olo'] = -$this->coin;
            }
        }

        //如果没有传入user_id、binggan、created_at，则使用此模型的饼干（简化传参）
        if (!array_key_exists('user_id', $income_statement)) {
            $income_statement['user_id'] = $this->id;
        }
        if (!array_key_exists('binggan', $income_statement)) {
            $income_statement['binggan'] = $this->binggan;
        }
        if (!array_key_exists('created_at', $income_statement)) {
            $income_statement['created_at'] = Carbon::now();
        }

        //执行异步的队列，记录olo变动操作
        ProcessIncomeStatement::dispatch($action, $income_statement);

        // $this->coin += $income_statement['olo'];
        $this->increment('coin', $income_statement['olo']);
        $this->save();

        //检查成就
        if ($income_statement['olo'] > 0) {
            //只有olo是增加的才需要检查
            UserMedalRecord::check_olo($this);
        } else {
            //检查olo清零的
            UserMedalRecord::check_olo_0($this);
        }
    }

    //获取总olo(现金+银行)
    public function getCoin()
    {
        return $this->coin + $this->coin_in_bank;
    }

    public function Pingbici()
    {
        return $this->hasOne(Pingbici::class);
    }

    public function MyEmoji()
    {
        return $this->hasOne(MyEmoji::class);
    }

    public function UserLV()
    {
        return $this->hasOne(UserLV::class);
    }

    public function AdminPermissions()
    {
        return $this->hasOne(Admin::class);
    }

    public function VoteUser()
    {
        return $this->hasMany(VoteUser::class);
    }

    public function IncomeStatement()
    {
        return $this->hasMany(IncomeStatement::class);
    }

    public function UserMedal()
    {
        return $this->hasMany(UserMedal::class);
    }


    public function UserMedalRecord()
    {
        return $this->hasOne(UserMedalRecord::class);
    }

    public function MyBattleChara()
    {
        return $this->hasMany(MyBattleChara::class);
    }

    public function EmojiContestUser()
    {
        return $this->hasMany(EmojiContestUser::class);
    }

    public function UserBank()
    {
        return $this->hasMany(UserBank::class);
    }

    public function UserMessages()
    {
        return $this->hasMany(UserMessages::class);
    }

    public function getAdminForumsAttribute()
    {
        return $this->AdminPermissions->forums;
    }


    public function getLockedTTLAttribute()
    {
        if ($this->locked_until != null && Carbon::parse($this->locked_until)->gt(Carbon::now())) {
            return Carbon::parse($this->locked_until)->diffInSeconds(Carbon::now(), true);  //返回差值int
        } else {
            return 0;
        }
    }

    public function getMyBattleCharaAttribute()
    {
        return MyBattleChara::where('user_id', $this->id)->pluck('name');
    }

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i');
    }

    public function lockedTTL()
    {
        if ($this->locked_until != null && Carbon::parse($this->locked_until)->gt(Carbon::now())) {
            return Carbon::parse($this->locked_until)->diffInSeconds(Carbon::now(), true);  //返回差值int
        } else {
            return 0;
        }
    }
}
