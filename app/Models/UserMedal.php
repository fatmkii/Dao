<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMedal extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "user_medals";

    protected $fillable = [
        'user_id',
    ];
    protected $hidden = [
        'id',
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function UserMedalRecord()
    {
        //不确定能用
        return $this->hasOneThrough(UserMedalRecord::class, User::class, 'id', 'user_id');
    }

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
