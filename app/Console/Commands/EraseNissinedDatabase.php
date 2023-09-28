<?php

namespace App\Console\Commands;

use App\Models\Battle;
use App\Models\BattleMessage;
use App\Models\Post;
use App\Models\Thread;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\PseudoTypes\False_;
use phpDocumentor\Reflection\Types\This;

class EraseNissinedDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EraseNissinedDatabase:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清空已日清的posts数据';

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
        if (!$this->confirm('确定要清空已日清的posts数据吗？记得先备份！')) {
            return False;
        }

        $this->info('正在开始清理数据');
        $nissined_threads_id = Thread::where('has_nissined', 1)
            ->where('forum_id', '<>', 419) //咒岛不清
            ->pluck('id');
        Log::channel('database_log')->info('nissined_thread_id', [$nissined_threads_id]);

        $num_nissined_threads = count($nissined_threads_id);
        $this->info(sprintf('已日清的主题数：%d个', $num_nissined_threads));
        Log::channel('database_log')->info(sprintf('已日清的主题数：%d个', $num_nissined_threads));


        $this->info('开始清理posts表');
        $bar = $this->output->createProgressBar($num_nissined_threads);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %message%');
        $bar->start();

        foreach ($nissined_threads_id as $index => $thread_id) {
            $num_posts = Post::suffix(intval($thread_id / 10000))
                ->where('thread_id', $thread_id)
                ->count();
            Post::suffix(intval($thread_id / 10000))
                ->where('thread_id', $thread_id)
                ->delete();
            $bar->setMessage(sprintf('已清理主题id-%d  共有%d个回复', $thread_id, $num_posts));
            Log::channel('database_log')->info(sprintf('已清理主题id-%d  共有%d个回复', $thread_id, $num_posts));
            $bar->advance();
        }
        $bar->finish();

        $this->info('开始清理battle_messages表');
        $bar = $this->output->createProgressBar($num_nissined_threads);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %message%');
        $bar->start();

        foreach ($nissined_threads_id as $index => $thread_id) {
            $battles_id = Battle::where('thread_id', $thread_id)->pluck('id');
            if ($battles_id != null) {
                $num_battle_messages = BattleMessage::whereIn('battle_id', $battles_id)->count();
                BattleMessage::whereIn('battle_id', $battles_id)->delete();
            } else {
                $num_battle_messages = 0;
            }
            $bar->setMessage(sprintf('已清理主题id-%d  共有%d个大乱斗信息', $thread_id, $num_battle_messages));
            Log::channel('database_log')->info(sprintf('已清理主题id-%d  共有%d个大乱斗信息', $thread_id, $num_battle_messages));
            $bar->advance();
        }
        $bar->finish();

        return True;
    }
}
