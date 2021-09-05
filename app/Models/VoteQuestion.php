<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteQuestion extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function VoteOption()
    {
        return $this->hasMany(VoteOption::class);
    }

    public function VoteUser()
    {
        return $this->hasMany(VoteUser::class);
    }

    public function Thread()
    {
        return $this->belongsTo(Thread::class);
    }
}
