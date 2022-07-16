<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\GambleUser;
use App\Models\GambleQuestion;

class ProcessGambleRepeal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $gamble_repeal;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($gamble_repeal)
    {
        $this->gamble_repeal = $gamble_repeal;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $gamble_question = GambleQuestion::find($this->gamble_repeal['gamble_question_id']);
        $thread = $gamble_question->thread;
        GambleUser::suffix(intval($this->gamble_repeal['gamble_question_id'] / 10000))
            ->where('gamble_question_id', $this->gamble_repeal['gamble_question_id'])
            ->chunk(5, function ($gamble_users) use ($thread) {
                foreach ($gamble_users as $gamble_user) {
                    // $gamble_user->user->coin += $gamble_user->betting_olo; //中止菠菜返回olo
                    // $gamble_user->user->save();
                    $user = $gamble_user->user;
                    $user->coinChange(
                        'normal', //记录类型
                        [
                            'olo' => $gamble_user->betting_olo,
                            'content' => '菠菜中止退回olo',
                            'thread_id' => $thread->id,
                            'thread_title' => $thread->title,
                        ]
                    ); //通过统一接口、记录操作  
                }
            });
    }
}
