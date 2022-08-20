<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\HongbaoPostUser;
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
            $hongbao_post_user = HongbaoPostUser::withTrashed()->where('user_id', $this->user_id)->where('hongbao_post_id', $this->id)->first();

            if ($hongbao_post_user) {
                return $hongbao_post_user;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }


    public function create(Request $request, $thread_id, $post_id, $floor)
    {
        $request->validate([
            'hongbao_olo' => 'required|integer|min:3000|max:1000000',
            'hongbao_num' => 'required|integer|min:1|max:50',
            'type' => 'required|integer',
            'hongbao_key_word' => 'required|string|max:255',
            'hongbao_message' => 'nullable|string|max:255',
        ]);

        $user = $request->user;

        $coin_pay = ceil($request->hongbao_olo * 1.07);
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

        $this->thread_id = $thread_id;
        $this->post_id = $post_id;
        $this->olo_total = $request->hongbao_olo;
        $this->num_total = $request->hongbao_num;
        $this->olo_remains = $request->hongbao_olo;
        $this->num_remains = $request->hongbao_num;
        $this->type = $request->type;
        $this->key_word = $request->hongbao_key_word;
        $this->message = $request->hongbao_message;
        $this->save();

        return $this->id;
    }
}
