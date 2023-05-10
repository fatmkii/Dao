<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\HongbaoPostUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HongbaoPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "hongbao_post";

    protected $user_id = 0; //用于查询是否已经领取过红包的数据

    public $hidden = [
        'thread_id',
        'post_id',
        'user_id',
        'olo_remains',
        'message',
        'created_binggan',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $appends = [
        'hongbao_user',
    ];

    public function HongbaoPostUser()
    {
        return $this->hasMany(HongbaoPostUser::class);
    }

    public function Thread()
    {
        return $this->belongsTo(Thread::class);
    }

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i');
    }

    public function setUserID($user_id)
    {
        $this->user_id = $user_id;
    }

    public function getHongbaoUserAttribute()
    {
        if ($this->user_id != 0) {
            $hongbao_post_user = HongbaoPostUser::where('user_id', $this->user_id)->where('hongbao_post_id', $this->id)->first();

            if ($hongbao_post_user) {
                return $hongbao_post_user;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    // public function getOloTotalAttribute($olo_total)
    // {
    //     if ($this->olo_hide) {
    //         //隐藏olo总额
    //         return null;
    //     } else {
    //         return $olo_total;
    //     }
    // }


    public static function create(Request $request, $thread_id, $post_id, $floor)
    {
        $request->validate([
            'hongbao_olo' => 'required|integer|min:3000|max:1000000',
            'hongbao_num' => 'required|integer|min:5|max:50',
            'type' => 'required|integer',
            'key_word_type' => 'nullable|integer',
            'hongbao_key_word' => 'required|string|max:255',
            'hongbao_question' => 'nullable|string|max:255',
            'hongbao_message' => 'nullable|string|max:255',
            'hongbao_olo_hide' => 'nullable|boolean',
        ]);

        $user = $request->user;


        if (Carbon::now()->between('2022/11/11 00:00:00', '2022/11/12 00:00:00')) {
            //双十一期间免税
            $tax_rate = 1;
        } else {
            $tax_rate = 1.07;
        }
        $coin_pay = ceil($request->hongbao_olo * $tax_rate);
        $user->coinChange(
            'normal', //记录类型
            [
                'olo' => -$coin_pay,
                'content' => '发起口令红包',
                'thread_id' => $thread_id,
                'post_id' => $post_id,
                'floor' => $floor,
                'thread_title' => $request->title,
            ]
        ); //扣除用户相应olo（通过统一接口、记录操作）

        $hongbao = new HongbaoPost();
        $hongbao->thread_id = $thread_id;
        $hongbao->post_id = $post_id;
        $hongbao->floor = $floor;
        $hongbao->olo_total = $request->hongbao_olo;
        $hongbao->num_total = $request->hongbao_num;
        $hongbao->olo_remains = $request->hongbao_olo;
        $hongbao->num_remains = $request->hongbao_num;
        $hongbao->type = $request->type;
        $hongbao->key_word_type = $request->key_word_type;
        $hongbao->key_word = $request->hongbao_key_word;
        $hongbao->message = $request->hongbao_message;
        $hongbao->question = $request->hongbao_question;
        $hongbao->olo_hide = $request->hongbao_olo_hide;
        $hongbao->save();

        $user_medal_record = $user->UserMedalRecord()->firstOrCreate();
        $user_medal_record->push_hongbao_out($request->hongbao_olo);

        return $hongbao;
    }
}
