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
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

use function Symfony\Component\VarDumper\Dumper\esc;

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
        'binggan',
        'admin',
        'password',
        'last_login',
        'created_ip',
        'created_at',
        'updated_at',
        'is_banned',
        'locked_until',
        'AdminPermissions',
        'pingbici',
        'MyEmoji',
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

    const NEW_THREAD_INTERVAL = 300; //发新主题频率
    const NEW_POST_NUMBER = 10; //回帖次数（也就是60秒10次）
    const NEW_POST_INTERVAL = 60; //回帖频率（含大乱斗）

    protected static function booted()
    {
        // static::retrieved(function ($user) {
        //     if ($user->admin != 0) {
        //         $user->append('admin_forums');
        //     }
        // });
    }


    //发帖、回帖频率检查
    public function waterCheck(string $action)
    {
        switch ($action) {
            case 'new_post': {
                    if (Redis::GET('new_post_record_' . $this->binggan) >= self::NEW_POST_NUMBER && $this->admin == 0) {
                        return response()->json([
                            'code' => ResponseCode::POST_TOO_MANY,
                            'message' => ResponseCode::$codeMap[ResponseCode::POST_TOO_MANY] . '为防止刷屏，每1分钟最多回帖10次（含大乱斗）',
                        ]);
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
    public function waterRecord(string $action)
    {
        switch ($action) {
            case 'new_post': {
                    if (Redis::exists('new_post_record_' . $this->binggan)) {
                        Redis::incr('new_post_record_' . $this->binggan);
                    } else {
                        Redis::setex('new_post_record_' . $this->binggan,  self::NEW_POST_INTERVAL, 1);
                    }
                    break;
                }
            case 'new_thread': {
                    Redis::setex('new_thread_record_' . $this->binggan, self::NEW_THREAD_INTERVAL, 1);
                    break;
                }
        }
    }
    
    //消费奥利奥并检查是否足够
    public function coinConsume(int $coin){
        if ($this->coin < $coin) {
            throw new CoinException();
        }
        $this->coin -= $coin;
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

    public function AdminPermissions()
    {
        return $this->hasOne(Admin::class);
    }

    public function VoteUser()
    {
        return $this->hasMany(VoteUser::class);
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
