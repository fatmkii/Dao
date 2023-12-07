<?php

namespace App\Console;

use App\Models\Loudspeaker;
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
        $schedule->command('BattlePolling:run')->everyMinute(); //过期大乱斗处理
        $schedule->command('DailyNissinHandle:run')->dailyAt('8:00'); //日清的处理
        $schedule->command('DelayThreadHandle:run')->dailyAt('8:00'); //延迟主题的处理，务必要要在日清处理之后！

        //定时重启Echo-server
        $schedule->exec('supervisorctl restart Laravel-Echo-server:Laravel-Echo-server_00')
            ->dailyAt('3:00')
            ->sendOutputTo('/var/log/Laravel-schedule.log');

        //定时检查全局搜索的redis计时器失效
        $schedule->call(function () {
            if (Redis::TTL('search_record_global') == -1) {
                Log::channel('my_log')->error('search_record_global expired failed');
                Redis::del('search_record_global');
            }
        })->everyMinute();

        //每分钟检查过期大喇叭
        $schedule->call(function () {
            Loudspeaker::where('expire_date', "<", Carbon::now())->delete();
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
