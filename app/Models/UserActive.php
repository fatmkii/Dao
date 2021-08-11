<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserActive extends myModel
{
    use HasFactory;

    protected $fillable = [
        'binggan',
        'user_id',
        'active',
        'post_id',
        'thread_id',
        'forum_id',
        'binggan_target',
        'content',
    ];

    public function User()
    {
        return $this->belongsTo(Thread::class);
    }
}
