<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VoteUser;

class VoteQuestion extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $hidden = [
        'thread_id',
    ];

    public function VoteOption()
    {
        return $this->hasMany(VoteOption::class);
    }

    public function VoteUserChoices($user_id)
    {
        return VoteUser::where('vote_question_id', $this->id)
            ->where('user_id', $user_id)->first();
    }

    public function Thread()
    {
        return $this->belongsTo(Thread::class);
    }
}
