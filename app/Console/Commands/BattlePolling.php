<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

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
        //清除过期的对战请求
        try {
            DB::beginTransaction();

            $battles_outdate = DB::table('battles')
                ->where('progress', '<=', 1)
                ->where('updated_at', "<", Carbon::now()->addMinutes(-5))
                ->get();

            foreach ($battles_outdate as $battle_outdate) {
                DB::table('users')->where('id', $battle_outdate->initiator_user_id)->increment('coin', $battle_outdate->battle_olo);
                if ($battle_outdate->progress == 1) {
                    DB::table('users')->where('id', $battle_outdate->challenger_user_id)->increment('coin', $battle_outdate->battle_olo);
                }

                DB::table('battle_messages')->insert(
                    [
                        'battle_id' => $battle_outdate->id,
                        'message_type' => 0,
                        'message' => '由于超过了5分钟，乱斗已结束。奥利奥已退回。',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]
                );
            }

            $battles_outdate = DB::table('battles')
                ->where('progress', '<=', 1)
                ->where('updated_at', "<", Carbon::now()->addMinutes(-5))
                ->update(['progress' => 3],);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return false;
        }

        return true;
    }
}
