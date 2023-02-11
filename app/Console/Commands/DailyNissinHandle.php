<?php

namespace App\Console\Commands;

use App\Models\Forum;
use App\Models\Thread;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DailyNissinHandle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DailyNissinHandle:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每日日清帖子处理';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //日清类型1处理（按照每日8点日清）
        $nissin_forums_id1 = Forum::where('is_nissin', 1)->pluck('id');
        DB::table('threads')
            ->whereIn('forum_id', $nissin_forums_id1)
            ->where('has_nissined',  0)
            ->where('sub_id', 0)
            ->where('is_delay', 0)
            ->update(['has_nissined' => 1]);

        //日清类型2处理（按帖子有效时间计算的日清）
        //实际上类型2并不会根据'has_nissined'字段处理，只是方便日后清数据
        $nissin_forums_id2 = Forum::where('is_nissin', 2)->pluck('id');
        DB::table('threads')
            ->whereIn('forum_id', $nissin_forums_id2)
            ->where('has_nissined',  0)
            ->where('sub_id', 0)
            ->where('is_delay', 0)
            ->where('nissin_date', '<', Carbon::today()->addHours(8))
            ->update(['has_nissined' => 1]);

        return true;
    }
}
