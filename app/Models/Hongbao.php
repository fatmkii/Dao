<?php

namespace App\Models;

use App\Facades\GlobalSetting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class Hongbao extends Model
{
    use HasFactory;

    protected $user_id = 0; //用于查询是否已经领取过红包的数据

    protected $table = 'hongbao';

    public $hidden = [
        'thread_id',
        'user_id',
        'olo_remains',
        'created_binggan',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'hongbao_user',
    ];


    public function HongbaoUser()
    {
        return $this->hasMany(HongbaoUser::class);
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
            $hongbao_user = HongbaoUser::where('user_id', $this->user_id)->where('hongbao_id', $this->id)->first();

            if ($hongbao_user) {
                return $hongbao_user;
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

    public static function create(Request $request, $thread_id)
    {
        $request->validate([
            'hongbao_olo' => 'required|integer|min:20000|max:2000000',
            'hongbao_num' => 'required|integer|min:1|max:600',
            'type' => 'required|integer',
            'hongbao_key_word' => 'required|string|max:255',
            'hongbao_message' => 'nullable|string|max:255',
            'hongbao_message_json' => 'nullable|json|max:3000',
            'hongbao_olo_hide' => 'nullable|boolean',
            'hongbao_loudspeaker' => 'nullable|boolean',
        ]);

        $user = $request->user;

        $tax_rate = GlobalSetting::get_tax('normal');
        $coin_pay = ceil($request->hongbao_olo * $tax_rate);
        $user->coinChange(
            'normal', //记录类型
            [
                'olo' => -$coin_pay,
                'content' => '发起红包主题',
                'thread_id' => $thread_id,
                'thread_title' => $request->title,
            ]
        ); //扣除用户相应olo（通过统一接口、记录操作）

        $hongbao = new Hongbao();
        $hongbao->thread_id = $thread_id;
        $hongbao->olo_total = $request->hongbao_olo;
        $hongbao->num_total = $request->hongbao_num;
        $hongbao->olo_remains = $request->hongbao_olo;
        $hongbao->num_remains = $request->hongbao_num;
        $hongbao->type = $request->type;
        $hongbao->key_word = $request->hongbao_key_word;
        $hongbao->message = $request->hongbao_message;
        $hongbao->message_json = $request->hongbao_message_json;
        $hongbao->olo_hide = $request->hongbao_olo_hide;
        $hongbao->save();

        if ($request->hongbao_loudspeaker) {
            //如果有设定自动发大喇叭，就发大喇叭
            $effective_date = Carbon::now();
            $expire_date = $effective_date->copy()->addDays(1); //记得copy否则会影响原变量
            $loudspeaker = Loudspeaker::create([
                'sub_id' => 1,
                'user_id' => $user->id,
                'created_binggan' => $user->binggan,
                'content' => $request->title,
                'thread_id' => $thread_id,
                'effective_date' => $effective_date,
                'expire_date' => $expire_date,
            ]);
        }

        $user_medal_record = $user->UserMedalRecord()->firstOrCreate();
        $user_medal_record->push_hongbao_out($request->hongbao_olo);

        return $hongbao;
    }
}
