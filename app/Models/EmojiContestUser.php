<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmojiContestUser extends Model
{
    use HasFactory;

    protected $table = "emoji_contest_user";

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'upadted_at',
    ];

    protected $fillable = [
        'user_id',
        'emoji_group_id',
        'emoji_id',
        'votes_num',
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
