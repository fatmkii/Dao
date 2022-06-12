<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\UserMessages;

class AnnoucementMessages extends Model
{
    use HasFactory, SoftDeletes;


    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'announcment_messages';


    protected $hidden = [
        'sender_id',
        'type',
        'to_new_users',
    ];

    public function UserMessages()
    {
        return $this->belongsTo(UserMessages::class);
    }
}
