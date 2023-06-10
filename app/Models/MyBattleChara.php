<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyBattleChara extends Model
{
    use HasFactory;

    protected $table = "my_battle_chara";

    protected $hidden = [
        'id',
        'user_id',
        'chara_id',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id',
        'chara_id',
        'name',
        'heads',
        'messages',
    ];

    protected $casts = [
        'messages' => 'array',
        'heads' => 'array'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
