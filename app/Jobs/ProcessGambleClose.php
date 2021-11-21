<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\GambleUser;

class ProcessGambleClose implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $gamble_close;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $gamble_close)
    {
        $this->gamble_close = $gamble_close;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $odds = $this->gamble_close['result_option']->odds;
        $result_option_id = $this->gamble_close['result_option']->id;
        GambleUser::suffix(intval($this->gamble_close['gamble_question_id'] / 10000))
            ->where('gamble_question_id', $this->gamble_close['gamble_question_id'])
            ->chunk(5, function ($gamble_users) use ($odds, $result_option_id) {
                foreach ($gamble_users as $gamble_user) {
                    if ($gamble_user->option_id == $result_option_id) {
                        $gamble_user->odds = $odds;
                        $gamble_user->win_olo = intval($gamble_user->betting_olo * $odds);
                        $gamble_user->user->coin += intval($gamble_user->betting_olo * $odds); //发放奖金
                    } else {
                        $gamble_user->odds = 0;
                        $gamble_user->win_olo = 0;
                    }
                    $gamble_user->save();
                    $gamble_user->user->save();
                }
            });
    }
}
