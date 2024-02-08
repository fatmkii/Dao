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
        $medals_id = array(11, 12, 13, 14, 15);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public function push_battle_out(int $olo)
    {
        $this->increment('battle_olo_out', -$olo); //注意此处$olo参数是个负数
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(21, 22, 23, 24, 25);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public function push_hongbao_in(int $olo)
    {
        //单个数字确定型的徽章，则先判断数字，以减少查询量
        if ($olo == 0) {
            //红包获得0个olo的成就
            $medals_code_exists = UserMedal::where('user_id', $this->user_id)->where('medal_id', 121)->exists();
            if (!$medals_code_exists) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = 121;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        } else {
            $this->increment('hongbao_olo_in', $olo);
            $this->save();

            //本次处理的同一系列的成就id
            $medals_id = array(31, 32, 33, 34, 35);

            //已经获得过的徽章（统一查询一次，用于后续判断）
            // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
            $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
            if (count($medals_id) == count($medals_id_retain)) {
                //如果数量一样，说明整个系列都已经获得，不需要再判断了
                return;
            }

            foreach ($medals_id as $medal_id) {
                //循环判断此系列的成就id是否满足阈值threshold条件
                $varname = Medals::DATA[$medal_id]['varname'];
                if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                    $user_medal = new UserMedal;
                    $user_medal->user_id = $this->user_id;
                    $user_medal->medal_id = $medal_id;
                    $user_medal->created_at = Carbon::now();
                    $user_medal->save();
                }
            }
        }
    }

    public function push_hongbao_out(int $olo)
    {
        $this->increment('hongbao_olo_out', $olo); //此处olo应是正数
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(41, 42, 43, 44, 45);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public function push_reward_in(int $olo)
    {

        $this->increment('reward_olo_in', $olo);
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(161, 162, 163, 164);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public function push_reward_out(int $olo)
    {

        $this->increment('reward_olo_out', $olo);
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(171, 172, 173, 174);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
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
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];

            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public function push_battle_win()
    {
        $this->increment('battle_win_con', 1);
        $this->battle_lose_con = 0;
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(221, 222, 223, 224);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];

            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public function push_battle_lose()
    {
        $this->increment('battle_lose_con', 1);
        $this->battle_win_con = 0;
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(231, 232, 233, 234);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];

            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
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
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
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
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
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
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
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
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            if (!in_array($medal_id, $medals_id_retain) && $user->coin >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $user->id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
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
                $user_medal->medal_id = 91;
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
        $medal_id = 131;
        //纯粹行为型的徽章，直接查询
        // $medals_code_exists = $this->UserMedal()->where('medal_id', 131)->exists();
        $medals_code_exists = UserMedal::where('user_id', $this->user_id)->where('medal_id', $medal_id)->exists();
        if (!$medals_code_exists) {
            $user_medal = new UserMedal;
            $user_medal->user_id = $this->user_id;
            $user_medal->medal_id = $medal_id;
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
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $user->user_lv >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $user->id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }


    public function check_anniversary()
    {

        if (Carbon::now() > Carbon::create("2023-5-7 0:0:0")) {
            //如果时间已经过期，就什么都不做
            return;
        }

        $medal_id = 151;

        //纯粹行为型的徽章，直接查询
        // $medals_code_exists = $this->UserMedal()->where('medal_id', 131)->exists();
        $medals_code_exists = UserMedal::where('user_id', $this->user_id)->where('medal_id', $medal_id)->exists();
        if (!$medals_code_exists) {
            $user_medal = new UserMedal;
            $user_medal->user_id = $this->user_id;
            $user_medal->medal_id = $medal_id;
            $user_medal->created_at = Carbon::now();
            $user_medal->save();
        }
    }

    public function check_national_day()
    {
        if (Carbon::now() < Carbon::create("2024-2-10 0:0:0")) {
            //如果时间未到，就什么都不做
            return;
        }

        if (Carbon::now() > Carbon::create("2024-2-13 0:0:0")) {
            //如果时间已经过期，就什么都不做
            return;
        }

        $medal_id = 153;

        //纯粹行为型的徽章，直接查询
        // $medals_code_exists = $this->UserMedal()->where('medal_id', 131)->exists();
        $medals_code_exists = UserMedal::where('user_id', $this->user_id)->where('medal_id', $medal_id)->exists();
        if (!$medals_code_exists) {
            $user_medal = new UserMedal;
            $user_medal->user_id = $this->user_id;
            $user_medal->medal_id = $medal_id;
            $user_medal->created_at = Carbon::now();
            $user_medal->save();
        }
    }


    public static function check_emoji_contest_group(int $group_id, int $votes_num_total, User $user)
    {
        //表情包萌成就判断（每个角色的成就）
        $medal_id = $group_id + 200; //emoji_group_id从1开始，相应medal_id是201，差额200

        $medals_code_exists = UserMedal::where('user_id', $user->id)->where('medal_id', $medal_id)->exists();
        // $votes_num_total = EmojiContestUserTotal::where('user_id', $user->id)->where('emoji_group_id', $group_id)->sum('votes_num_total');

        if (!$medals_code_exists && $votes_num_total >= Medals::DATA[$medal_id]['threshold']) {
            $user_medal = new UserMedal;
            $user_medal->user_id = $user->id;
            $user_medal->medal_id = $medal_id;
            $user_medal->created_at = Carbon::now();
            $user_medal->save();
        }
    }

    public static function check_emoji_contest_total(User $user)
    {
        //表情包萌成就判断（总计的投票数）
        //本次处理的同一系列的成就id
        // $medals_id = array(212, 213);//212和213是表情包萌
        $medals_id = array(261, 262); //261和262是表情包萌

        //已经获得过的徽章（统一查询一次，用于后续判断）
        $medals_id_retain = UserMedal::where('user_id', $user->id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        $user_votes_total = EmojiContestUserTotal::where('user_id', $user->id)->sum('votes_num_total');

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            if (!in_array($medal_id, $medals_id_retain) && $user_votes_total >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $user->id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public static function check_end_travel(int $thread_id, User $user)
    {
        //单独用于判断“小火锅终末旅行”成就的
        if ($thread_id == 8283 && $user) {
            UserMedal::firstOrCreate(
                ['user_id' => $user->id, 'medal_id' => 183],
                ['user_id' => $user->id, 'medal_id' => 183, 'created_at' => Carbon::now()]
            );
        }
    }

    public function push_withdraw_penalty(int $olo)
    {

        $this->increment('withdraw_penalty', $olo);
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(251);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public static function check_bank_coin(User $user)
    {
        //本次处理的同一系列的成就id
        $medals_id = array(241, 242, 243, 244, 245);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $user->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $user->id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            // $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $user->coin_in_bank >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $user->id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public function push_loudspeakers_con()
    {
        $this->increment('loudspeakers_con', 1);
        $this->save();

        //本次处理的同一系列的成就id
        $medals_id = array(271, 272, 273, 274);

        //已经获得过的徽章（统一查询一次，用于后续判断）
        // $medals_id_retain = $this->UserMedal()->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        $medals_id_retain = UserMedal::where('user_id', $this->user_id)->whereIn('medal_id', $medals_id)->pluck('medal_id')->toArray();
        if (count($medals_id) == count($medals_id_retain)) {
            //如果数量一样，说明整个系列都已经获得，不需要再判断了
            return;
        }

        foreach ($medals_id as $medal_id) {
            //循环判断此系列的成就id是否满足阈值threshold条件
            $varname = Medals::DATA[$medal_id]['varname'];
            if (!in_array($medal_id, $medals_id_retain) && $this->$varname >= Medals::DATA[$medal_id]['threshold']) {
                $user_medal = new UserMedal;
                $user_medal->user_id = $this->user_id;
                $user_medal->medal_id = $medal_id;
                $user_medal->created_at = Carbon::now();
                $user_medal->save();
            }
        }
    }

    public function check_loudspeakers_withdraw()
    {

        $medal_id = 270;

        //纯粹行为型的徽章，直接查询
        // $medals_code_exists = $this->UserMedal()->where('medal_id', 131)->exists();
        $medals_code_exists = UserMedal::where('user_id', $this->user_id)->where('medal_id', $medal_id)->exists();
        if (!$medals_code_exists) {
            $user_medal = new UserMedal;
            $user_medal->user_id = $this->user_id;
            $user_medal->medal_id = $medal_id;
            $user_medal->created_at = Carbon::now();
            $user_medal->save();
        }
    }
}
