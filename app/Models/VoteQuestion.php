<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VoteUser;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VoteQuestion extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $hidden = [
        'thread_id',
        'vote_total',
        'created_at',
        'updated_at',
        'VoteOption',
    ];

    public function VoteOption()
    {
        return $this->hasMany(VoteOption::class);
    }

    public function VoteUserChoices($user_id)
    {
        return VoteUser::suffix(intval($this->id / 10000))->where('vote_question_id', $this->id)
            ->where('user_id', $user_id)->first();
    }

    public function Thread()
    {
        return $this->belongsTo(Thread::class);
    }

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function create(Request $request, $thread_id)
    {
        $request->validate([
            'vote_title' => 'required|string|max:100',
            'vote_end_time' => 'required|date_format:Y-m-d H:i:s|before:' . Carbon::now()->addMonth(1), //投票时长最多一个月
            'vote_options' => 'json|required|max:5000',
            'vote_multiple' => 'boolean|required'
        ]);

        $request_options = json_decode($request->vote_options, true);

        $this->thread_id = $thread_id;
        $this->title = $request->vote_title;
        $this->end_date = Carbon::parse($request->vote_end_time);
        $this->multiple = $request->vote_multiple;
        $this->save();

        foreach ($request_options as $request_option) {
            $options = new VoteOption;
            $options->vote_question_id = $this->id;
            $options->option_text = $request_option;
            $options->save();
        }

        return $this->id;
    }
}
