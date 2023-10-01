<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HongbaoPoolFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'HongbaoPoolFix:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复祝福池的错误（变成加olo了）';

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
        if (!$this->confirm('是否开始修复？')) {
            return False;
        }
        $this->info("开始修复");

        $user_incomes = DB::table("income_statement_2023")
            ->where('id', '>', 4953691)
            ->where('content', '国庆祝福池')
            ->where('olo', 1000)
            ->get(['id', 'user_id']);

        Log::channel('temp_log')->info('出错的user_ids', [$user_incomes]);
        $this->info('已找出出错饼干数量：' . count($user_incomes));
        $this->info('记录：' . $user_incomes);
        if (!$this->confirm('是否继续？')) {
            return False;
        }

        foreach ($user_incomes as $key => $user_income) {
            $user = User::find($user_income->user_id);
            if ($user->coin > 2000) {
                $user->coinConsume(2000);
                DB::table("income_statement_2023")
                    ->where('id', $user_income->id)
                    ->update(['olo' => -1000]);
                Log::channel('temp_log')->info('已处理user:', [$user->id]);
                $this->info('已处理user:', $user->id);
            } else {
                Log::channel('temp_log')->info('user olo不足2000，保留:', [$user->id]);
                $this->info('user olo不足，保留:', $user->id);
            }
        }
        Log::channel('temp_log')->info('处理结束');
        $this->info('处理结束');

        return true;
    }
}
