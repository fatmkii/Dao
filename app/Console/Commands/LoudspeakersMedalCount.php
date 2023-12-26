<?php

namespace App\Console\Commands;

use App\Models\UserMedalRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\Cast\Array_;

class LoudspeakersMedalCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LoudspeakersMedalCount:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计并写入更新前的大喇叭成就数据（发布大喇叭次数）';

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
        $arr = DB::table('loudspeakers')->select(DB::raw('user_id, count(*) as user_count'))->groupBy('user_id')->get();
        $this->info('记录：' . json_encode($arr));
        Log::channel('temp_log')->info('loudspeakers_count', [$arr]);

        // foreach ($arr as $key => $user_data) {
        //     //检查成就（发布大喇叭）
        //     $user_medal_record = UserMedalRecord::firstOrNew(['user_id' => $user_data->user_id]); //如果记录不存在就追加
        //     $user_medal_record->loudspeakers_con = $user_data->user_count;
        //     $user_medal_record->save();
        // }

        // $this->info('已结束处理');
        // return 0;
    }
}
