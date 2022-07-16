<?php

namespace App\Jobs;

use App\Models\Crowd;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CrowdRecord;

class ProcessCrowdRepeal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $crowd_repeal;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($crowd_repeal)
    {
        $this->crowd_repeal = $crowd_repeal;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $crowd = Crowd::find($this->crowd_repeal['crowd_id']);
        $thread = $crowd->thread;
        CrowdRecord::where('crowd_id', $this->crowd_repeal['crowd_id'])
            ->chunk(5, function ($crowd_records) use ($thread) {
                foreach ($crowd_records as $crowd_record) {
                    // $crowd_record->user->coin += $crowd_record->olo; //中止众筹返回olo
                    // $crowd_record->user->save();
                    $user = $crowd_record->user;
                    $user->coinChange(
                        'normal', //记录类型
                        [
                            'olo' => $crowd_record->olo,
                            'content' => '众筹中止退回olo',
                            'thread_id' => $thread->id,
                            'thread_title' => $thread->title,
                        ]
                    ); //扣除用户相应olo（通过统一接口、记录操作）
                }
            });
    }
}
