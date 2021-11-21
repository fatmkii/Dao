<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GambleQuestion;

class GambleOption extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $hidden = [
        'gamble_question_id',
        'created_at',
        'updated_at',
    ];

    public function GambleQuestion()
    {
        return $this->belongsTo(GambleQuestion::class);
    }
}
