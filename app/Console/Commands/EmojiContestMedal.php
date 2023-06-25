<?php

namespace App\Console\Commands;

use App\Models\EmojiContestUserTotal;
use App\Models\UserMedal;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class EmojiContestMedal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EmojiContestMedal:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发放冠军成就徽章';

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
        $user_ids = EmojiContestUserTotal::distinct()->pluck('user_id');
        $count = 0;
        $user_array = [];

        Log::channel('temp_log')->info('user_ids', [$user_ids]);
        Log::channel('temp_log')->info('count(user_ids)', [count($user_ids)]);
        foreach ($user_ids as $key => $user_id) {
            $user_vote_total = EmojiContestUserTotal::where('user_id', $user_id)
                ->orderBy('votes_num_total', 'desc')
                ->orderBy('updated_at', 'desc')
                ->first();
            if ($user_vote_total->emoji_group_id == 8) {
                $count += 1;
                array_push($user_array, $user_id);
                UserMedal::create([
                    'user_id' => $user_id,
                    'medal_id' => 214,
                    'created_at' => Carbon::now(),
                ]);
            }
        }
        Log::channel('temp_log')->info('发放user_id', [$user_array]);
        Log::channel('temp_log')->info('合计发放个数：', [$count]);
        return 0;
    }
}
