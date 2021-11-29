<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BattlePolling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BattlePolling:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '轮询消除过期的对战请求';

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
        return 0;

        DB::table('battles')->where('id', 10001)->increment('bet_olo');
        return 0;
    }
}
