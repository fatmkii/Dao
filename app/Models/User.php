<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Common\ResponseCode;
use App\Models\Pingbici;
use App\Models\MyEmoji;
use App\Models\Admin;
use Carbon\Carbon;

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

    protected static function booted()
    {
        // static::retrieved(function ($user) {
        //     if ($user->admin != 0) {
        //         $user->append('admin_forums');
        //     }
        // });
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
