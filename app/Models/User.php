<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
    const NEW_POST_NUMBER_IP2 = 5; //IP回帖不看帖次数
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
    public function waterCheck(string $action, string $ip, int $thread_id = null)
    {
        switch ($action) {
            case 'new_post': {
                    //第1类检查：饼干记录，1分钟最多10贴
                    $new_post_record = Redis::GET('new_post_record_' . $this->binggan);
                    if ($new_post_record >= self::NEW_POST_NUMBER && $this->admin == 0) {
                        return response()->json([
                            'code' => ResponseCode::POST_TOO_MANY,
                            'message' => ResponseCode::$codeMap[ResponseCode::POST_TOO_MANY] . '为防止刷屏，每1分钟最多回帖10次（含大乱斗）',
                        ]);
                    }
                    //第2类检查：IP记录，3600秒内100次弹出验证码
                    $new_post_record_IP = Redis::GET('new_post_record_IP_' . $ip);
                    if ($new_post_record_IP >= self::NEW_POST_NUMBER_IP && $this->admin == 0) {

                        ProcessUserActive::dispatch(
                            [
                                'binggan' => $this->binggan,
                                'user_id' => $this->id,
                                'active' => '用户触发了机器人刷帖警报',
                                'content' => 'ip:' . $ip . ' record:' . $new_post_record_IP,
                            ]
                        );

                        //第2.2类检查：弹出验证码时，回顾该用户前10个帖子的毫秒值看是否相似
                        if ($thread_id != null) {
                            $user_posts_millis = Post::suffix(intval($thread_id / 10000))
                                ->where('created_binggan', $this->binggan)
                                ->orderBy('id', 'desc')->limit(10)->pluck('milliseccond');

                            list($avg, $variance) = $this->get_avg_var($user_posts_millis);

                            if ($variance < 1000) {
                                ProcessUserActive::dispatch(
                                    [
                                        'binggan' => $this->binggan,
                                        'user_id' => $this->id,
                                        'active' => '怀疑用户用脚本刷帖(毫秒值相似)',
                                        'content' => 'ip:' . $ip . ' avg:' . $avg . ' var:' . $variance,
                                    ]
                                );
                            }
                        }
                        return response()->json([
                            'code' => ResponseCode::POST_TOO_MANY_MAYBE_ROBOT,
                            'message' => ResponseCode::$codeMap[ResponseCode::POST_TOO_MANY_MAYBE_ROBOT],
                        ]);
                    }

                    //第3类检查：IP记录，只回帖不看帖（防止直接操作API的脚本）
                    $new_post_record_IP2 = Redis::GET('new_post_record_IP2_' . $ip);
                    if ($new_post_record_IP2 >= self::NEW_POST_NUMBER_IP2 && $new_post_record_IP2 % 5 == 0 && $this->admin == 0) {
                        ProcessUserActive::dispatch(
                            [
                                'binggan' => $this->binggan,
                                'user_id' => $this->id,
                                'active' => '怀疑用户用脚本刷帖(回帖不看帖)',
                                'content' => 'ip:' . $ip . ' record:' . $new_post_record_IP2,
                            ]
                        );
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
                    if (Redis::exists('new_post_record_' . $this->binggan)) {
                        Redis::incr('new_post_record_' . $this->binggan);
                    } else {
                        Redis::setex('new_post_record_' . $this->binggan,  self::NEW_POST_INTERVAL, 1);
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
        $this->coin -= $coin;
        $this->save();
    }

    //统一的奥利奥变更接口，并且留下income_statement记录
    public function coinChange(string $action = "normal", array $income_statement)
    {
        //检查olo是否足够
        if ($income_statement['olo'] < 0 && $this->coin < -$income_statement['olo']) {
            throw new CoinException();
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

        $this->coin += $income_statement['olo'];
        $this->save();
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
