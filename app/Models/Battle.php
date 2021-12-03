<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;
use App\Models\User;
use App\Models\BattleMessage;

class Battle extends Model
{
    use HasFactory;

    protected $binggan = '';

    protected $guarded = [];

    protected $appends = [
        'is_your_battle',
    ];

    public $hidden = [
        'post_id',
        'initiator_user_id',
        'initiator_chara',
        'challenger_user_id',
        'challenger_chara',
        'challenger_rand_num',
        'initiator_rand_num',
        'created_at',
        'updated_at',
    ];

    public function Post()
    {
        return $this->belongsTo(Post::class);
    }

    public function InitiatorUser()
    {
        return $this->belongsTo(User::class, 'initiator_user_id');
    }

    public function ChallengerUser()
    {
        return $this->belongsTo(User::class, 'challenger_user_id');
    }

    public function BattleMessages()
    {
        return $this->hasMany(BattleMessage::class);
    }

    public function setBinggan($binggan)
    {
        $this->binggan = $binggan;
    }

    public static function Binggan($binggan)
    {
        $instance = new static;
        $instance->setBinggan($binggan);

        return $instance->newQuery();
    }


    public function getIsYourBattleAttribute()
    {
        if ($this->binggan) {
            if ($this->binggan == $this->created_binggan) {
                return true;
            } else {
                return false;
            }
        }
    }
}
