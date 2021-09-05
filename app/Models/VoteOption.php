<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class VoteOption extends Model
{
    use HasFactory;

    protected $guarded = [];
<<<<<<< Updated upstream
=======
    public $hidden = [
        'vote_question_id',
    ];
>>>>>>> Stashed changes

    public function VoteQuestion()
    {
        return $this->belongsTo(VoteQuestion::class);
    }
}
