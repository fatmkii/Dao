<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GambleOption;
use App\Models\GambleUser;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class GambleQuestion extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $hidden = [
        'thread_id',
        'created_at',
        'updated_at',
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


    public function create(Request $request, $thread_id)
    {
        $request->validate([
            'gamble_title' => 'required|string|max:100',
            'gamble_end_time' => 'integer|required',
            'gamble_options' => 'json|required|max:5000'
        ]);

        $request_options = json_decode($request->gamble_options, true);

        $this->thread_id = $thread_id;
        $this->title = $request->gamble_title;
        $this->end_date = Carbon::now()->addSeconds($request->gamble_end_time);
        $this->save();

        foreach ($request_options as $request_option) {
            $options = new GambleOption;
            $options->gamble_question_id = $this->id;
            $options->option_text = $request_option;
            $options->save();
        }

        return $this->id;
    }
}