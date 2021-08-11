<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\Post;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class PostProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $reply;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Post $reply)
    {
        $this->reply = $reply;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $this->reply->save();
        Redis::set('bkey','bvalue');
        // return 1;
    }
}
