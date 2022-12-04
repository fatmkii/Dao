<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Carbon\Carbon;
use App\Common\Medals;
use Illuminate\Support\Facades\Log;

class UserMedalRecord extends Model
{
    use HasFactory;

    protected $table = "user_medal_record";

    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id',
        'medal_id',
        'created_at',
    ];


    //没在用
    public static function findOrCreateFromUserId($user_id)
    {
        $user_achievement = static::where('user_id', $user_id)->first();
        if (!$user_achievement) {
            $user_achievement = new static;
            $user_achievement->user_id = $user_id;
            $user_achievement->save();
        }
        return $user_achievement;
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    // public function UserMedal()
    // {
    //     //不确定能用
    //     return $this->hasManyThrough(UserMedal::class, User::class, 'id', 'user_id');
    // }

    public function push_battle_in(int $olo)
    {
        $this->increment('battle_olo_in', $olo);
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(11, 12, 13, 14);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
                break;
            }
        }
    }

    public function push_battle_out(int $olo)
    {
        $this->increment('battle_olo_out', -$olo); //注意此处$olo参数是个负数
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(21, 22, 23, 24);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
                break;
            }
        }
    }

    public function push_hongbao_in(int $olo)
    {
        $this->increment('hongbao_olo_in', $olo);
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(31, 32, 33, 34);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
                break;
            }
        }
    }

    public function push_hongbao_out(int $olo)
    {
        $this->increment('hongbao_olo_out', $olo); //此处olo应是正数
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(41, 42, 43, 44);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
                break;
            }
        }
    }

    public function push_battle_ignored()
    {
        $this->increment('battle_ignored', 1);
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(51, 52);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];

            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
                break;
            }
        }
    }

    public function check_battle_draw()
    {
        $this->increment('battle_draw', 1);
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(111, 112);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
                break;
            }
        }
    }


    public function check_delete_posts_num()
    {
        $this->increment('delete_posts_num', 1);
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(61, 62);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
                break;
            }
        }
    }

    public function push_posts_num()
    {
        $this->increment('posts_num', 1);
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(81, 82, 83, 84, 85);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
                break;
            }
        }
    }

    public static function check_olo(User $user)
    {
        //本次处理的同一系列的成就id
        $medals_id = array(1, 2, 3, 4); //71是olo归零

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $user->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $user->id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            if (!in_array($medal_id, $medals_id_retain) && $user->coin >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $user->id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
                break;
            }
        }
    }

    public static function check_olo_0(User $user)
    {
        //单独用于判断olo清零成就的
        //单个数字确定型的徽章，则先判断数字，以减少查询量
        if ($user->coin == 0) {
            // $medals_code_exists = $user->UserMedal()->where('medal_id', 71)->exists();
            $medals_code_exists = UserMedal::where('user_id', $user->id)->where('medal_id', 71)->exists();
            if (!$medals_code_exists) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $user->id;
                $user_medal->medal_id = 71;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public static function check_floor(int $floor, User $user)
    {
        //单个数字确定型的徽章，则先判断数字，以减少查询量
        if ($floor == 10000) {
            // $medals_code_exists = $user->UserMedal()->where('medal_id', 91)->exists();
            $medals_code_exists = UserMedal::where('user_id', $user->id)->where('medal_id', 91)->exists();
            if (!$medals_code_exists) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $user->id;
                $user_medal->medal_id = 92;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public function check_battle_num(int $battle_num)
    {
        //掷出大于100点的成就
        //单个数字确定型的徽章，则先判断数字，以减少查询量
        if ($battle_num > 100) {
            // $medals_code_exists = $this->UserMedal()->where('medal_id', 101)->exists();
            $medals_code_exists = UserMedal::where('user_id', $this->user_id)->where('medal_id', 101)->exists();
            if (!$medals_code_exists) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = 101;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public function check_custom_binggan()
    {
        //纯粹行为型的徽章，直接查询
        // $medals_code_exists = $this->UserMedal()->where('medal_id', 131)->exists();
        $medals_code_exists = UserMedal::where('user_id', $this->user_id)->where('medal_id', 131)->exists();
        if (!$medals_code_exists) {
            $user_medal = new UserMedal;
            $user_medal->user_id = $this->user_id;
            $user_medal->medal_id = 131;
            $user_medal->created_at = Carbon::now();
            $user_medal->save();
        }
    }

    public static function check_user_lv(User $user)
    {
        //本次处理的同一系列的成就id
        $medals_id = array(141, 142, 143, 144);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $user->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $user->id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $user->user_lv >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $user->id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
                break;
            }
        }
    }
}
