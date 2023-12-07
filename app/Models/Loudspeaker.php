<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loudspeaker extends Model
{
    use HasFactory, SoftDeletes;

    protected $binggan = '';

    protected $fillable = [
        'sub_id',
        'user_id',
        'created_binggan',
        'thread_id',
        'content',
        'color',
        'effective_date',
        'expire_date',
    ];

    protected $hidden = [
        'user_id',
        'created_binggan',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'is_your_loudspeaker',
    ];


    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }


    public function setBinggan($binggan)
    {
        $this->binggan = $binggan;
    }

    public function getIsYourLoudspeakerAttribute()
    {
        if ($this->binggan) {
            if ($this->binggan == $this->created_binggan) {
                return true;
            } else {
                return false;
            }
        } else {
            return null;
        }
    }
}
