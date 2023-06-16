<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmojiContest extends Model
{
    use HasFactory;

    protected $table = "emoji_contest";

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'user_id',
        'emoji_group_id',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'emoji_group_id',
        'emoji_id',
        'votes_num_total',
    ];
}
