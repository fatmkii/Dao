<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GambleOption;
use App\Models\GambleUser;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Validation\Validator;

class GambleQuestion extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $hidden = [
        'thread_id',
        'created_at',
        'updated_at',
        'GambleOptions',
        'GambleUsers',
    ];

    public function GambleOptions()
    {
        return $this->hasMany(GambleOption::class);
    }

    public function GambleUsers()
    {
        $gamble_users = new GambleUser;
        $gamble_users->setSuffix(intval($this->id / 10000));
        return new HasMany($gamble_users->newQuery(), $this, 'gamble_question_id', 'id');
    }


    public function Thread()
    {
        return $this->belongsTo(Thread::class);
    }


    public function GambleUserChoices($user_id)
    {
        return GambleUser::suffix(intval($this->id / 10000))->where('gamble_question_id', $this->id)
            ->where('user_id', $user_id)->get();  //和vote不同，gamble返回的是collection类
    }

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }


    public static function create(Request $request, $thread_id)
    {
        $request->validate([
            'gamble_title' => 'required|string|max:100',
            'gamble_end_time' => 'required|date_format:Y-m-d H:i:s|before:' . Carbon::now()->addMonth(1), //菠菜时长最多一个月
            'gamble_options' => 'json|required|max:5000'
        ]);


        $request_options = json_decode($request->gamble_options, true);

        $gamble_question = new GambleQuestion();
        $gamble_question->thread_id = $thread_id;
        $gamble_question->title = $request->gamble_title;
        $gamble_question->end_date = Carbon::parse($request->gamble_end_time);
        $gamble_question->save();

        foreach ($request_options as $request_option) {
            $options = new GambleOption;
            $options->gamble_question_id = $gamble_question->id;
            $options->option_text = $request_option;
            $options->save();
        }

        return $gamble_question;
    }
}
