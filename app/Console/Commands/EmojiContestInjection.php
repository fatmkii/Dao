<?php

namespace App\Console\Commands;

use App\Models\EmojiContest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EmojiContestInjection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EmojiContestInjection:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '注入表情包数据到数据库';

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
        $file_path = app_path('Console/Commands/emoji_moe_output_PHP.json');
        $json_string = file_get_contents($file_path);
        $json_data = json_decode($json_string);
        foreach ($json_data as $emoji_group_id => $emoji_group) {
            foreach ($emoji_group as $emoji_id) {
                EmojiContest::create([
                    'emoji_group_id' => $emoji_group_id + 1, //从1开始的
                    'emoji_id' => $emoji_id,
                    'votes_num_total' => 0,
                ]);
            }
        }
        return true;
    }
}
