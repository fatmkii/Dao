<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< Updated upstream
=======
use App\Models\VoteUser;
>>>>>>> Stashed changes

class VoteQuestion extends Model
{
    use HasFactory;

    protected $guarded = [];
<<<<<<< Updated upstream
=======
    public $hidden = [
        'thread_id',
    ];
>>>>>>> Stashed changes

    public function VoteOption()
    {
        return $this->hasMany(VoteOption::class);
    }

<<<<<<< Updated upstream
    public function VoteUser()
    {
        return $this->hasMany(VoteUser::class);
=======
    public function VoteUserChoices($user_id)
    {
        return VoteUser::where('vote_question_id', $this->id)
            ->where('user_id', $user_id)->first();
>>>>>>> Stashed changes
    }

    public function Thread()
    {
        return $this->belongsTo(Thread::class);
    }
}
