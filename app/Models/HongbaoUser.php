<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HongbaoUser extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    public function Hongbao()
    {
        return $this->belongsTo(Hongbao::class);
    }

}
