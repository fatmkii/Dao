<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Crowd;
use App\Models\User;

class CrowdRecord extends Model
{
    use HasFactory;

    public $hidden = [
        'user_id',
        'crowd_id',
        'updated_at',
    ];

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i');
    }

    public function Crowd()
    {
        return $this->belongsTo(Crowd::class);
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
