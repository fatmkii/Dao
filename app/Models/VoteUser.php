<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteUser extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function VoteQuestion()
    {
        return $this->belongsTo(VoteQuestion::class);
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
