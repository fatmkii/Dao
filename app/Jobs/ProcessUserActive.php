<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\UserActive;
use Illuminate\Support\Carbon;

class ProcessUserActive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_active;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Array $user_active)
    {
        $this->user_active = $user_active;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $model = new UserActive($this->user_active);
        $model->setsuffix(Carbon::now()->year . '_' . Carbon::now()->month);
        $model->save();
    }
}
