<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AnnouncementMessages;

class UserMessages extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'messages_index';


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
    ];


    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function AnnouncementMessages()
    {
        return $this->hasMany(AnnouncementMessages::class, "announcement_id", "id");
    }
}
