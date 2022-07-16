<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserLV extends Model
{
    use HasFactory;

    protected $table = 'users_LV';

    public $hidden = [
        'id',
        'user_id',
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
