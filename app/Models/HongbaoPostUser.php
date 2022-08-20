<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HongbaoPostUser extends Model
{
    use HasFactory;

    protected $table = "hongbao_post_users";

    public $timestamps = false;

    public $hidden = [
        'user_id',
        'created_at',
    ];

    public function HongbaoPost()
    {
        return $this->belongsTo(HongbaoPost::class);
    }
}
