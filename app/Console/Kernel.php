<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('BattlePolling:run')->everyMinute();
        $schedule->command('DelayThreadHandle:run')->dailyAt('8:00');
        $schedule->exec('supervisorctl restart Laravel-Echo-server:Laravel-Echo-server_00')
            ->dailyAt('3:00')
            ->sendOutputTo('/var/log/Laravel-schedule.log');
        $schedule->call(function () {
            if (Redis::TTL('search_record_global') == -1) {
                Log::error('search_record_global expired failed');
                Redis::del('search_record_global');
            } 
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
