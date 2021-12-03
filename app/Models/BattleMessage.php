<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Battle;

class BattleMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $hidden = [
        'id',
        'post_id',
        'created_at',
        'updated_at',
    ];

    public function Battle()
    {
        return $this->belongsTo(Battle::class);
    }
}
