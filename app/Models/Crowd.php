<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CrowdRecord;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class Crowd extends Model
{
    use HasFactory;

    public $hidden = [
        'thread_id',
        'created_at',
        'updated_at',
    ];

    public function CrowdRecord()
    {
        return $this->hasMany(CrowdRecord::class);
    }

    public function Thread()
    {
        return $this->belongsTo(Thread::class);
    }

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function CrowdUserRecord($user_id)
    {
        return CrowdRecord::where('crowd_id', $this->id)
            ->where('user_id', $user_id)->get();  //和vote不同，crowd返回的是collection类
    }

    public function create(Request $request, $thread_id)
    {
        $request->validate([
            'crowd_title' => 'required|string|max:100',
            'crowd_end_time' => 'required|date_format:Y-m-d H:i:s|before:' . Carbon::now()->addMonth(1), //有效期时长最多一个月
            'crowd_olo_target' => 'required|integer|max:10000000',
        ]);

        $this->thread_id = $thread_id;
        $this->title = $request->crowd_title;
        $this->end_date = Carbon::parse($request->vote_end_time);
        $this->olo_target = $request->crowd_olo_target;
        $this->save();

        return $this->id;
    }
}
