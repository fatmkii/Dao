<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\myModel;

class VoteUser extends myModel
{
    use HasFactory;

    protected $guarded = [];

    public $hidden = [
        'user_id',
        'vote_question_id',

    ];

    public function VoteQuestion()
    {
        return $this->belongsTo(VoteQuestion::class);
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
