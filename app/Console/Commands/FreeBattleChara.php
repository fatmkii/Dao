<?php

namespace App\Console\Commands;

use App\Common\BattleChara;
use App\Models\MyBattleChara;
use App\Models\User;
use App\Models\UserLV;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FreeBattleChara extends Command
{

    //UserLV相关
    const MYEMOJI_MIN = 5000;  //我的表情包初始长度
    const MYEMOJI_MAX = 30000;  //我的表情包最大长度
    const MYEMOJI_INTERVAL = 1000;  //我的表情包每次升级增加长度
    const MYEMOJI_OLO = -20000;  //我的表情包每次升级消费olo

    const TITLE_PINGBICI_MIN = 1000;  //标题屏蔽词初始长度
    const TITLE_PINGBICI_MAX = 4000;  //标题屏蔽词最大长度
    const TITLE_PINGBICI_INTERVAL = 200;  //标题屏蔽词每次升级增加长度
    const TITLE_PINGBICI_OLO = -4000;  //我的表情包每次升级消费olo

    const CONTENT_PINGBICI_MIN = 1000;  //内容屏蔽词每次初始长度
    const CONTENT_PINGBICI_MAX = 4000;  //内容屏蔽词每次最大长度
    const CONTENT_PINGBICI_INTERVAL = 200; //内容屏蔽词每次升级增加长度
    const CONTENT_PINGBICI_OLO = -4000;  //我的表情包每次升级消费olo

    const FJF_PINGBICI_MIN = 1000;  //反精分屏蔽词初始长度
    const FJF_PINGBICI_MAX = 4000;  //反精分屏蔽词最大长度
    const FJF_PINGBICI_INTERVAL = 200;  //反精分屏蔽词每次升级增加长度
    const FJF_PINGBICI_OLO = -4000;  //我的表情包每次升级消费olo

    const MYBATTLECHARA_MIN = 0;  //自定义大乱斗角色初始数量
    const MYBATTLECHARA_MAX = 3;  //自定义大乱斗角色最大数量
    const MYBATTLECHARA = -50000;  //自定义大乱斗角色每次升级消费olo

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'FreeBattleChara:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '（一次性使用）给所有用户发放1个自定义大乱斗槽位';

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
        if (!$this->confirm('确定运行吗')) {
            return False;
        }

        $this->info('开始处理');
        $user_count = User::count();
        $bar = $this->output->createProgressBar($user_count);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %message%');
        $bar->start();


        User::where('id', '<=', 135795)->chunk(100, function ($users) use ($bar) {
            foreach ($users as $user) {
                if ($user->last_login > Carbon::parse('2023-01-01 0:0:0')) {
                    $user_lv = $user->UserLV;
                    if ($user_lv == null) {
                        $user_lv = new UserLV();
                        $user_lv->user_id = $user->id;
                        $user_lv->title_pingbici = self::TITLE_PINGBICI_MIN;
                        $user_lv->content_pingbici = self::CONTENT_PINGBICI_MIN;
                        $user_lv->fjf_pingbici = self::FJF_PINGBICI_MIN;
                        $user_lv->my_emoji = self::MYEMOJI_MIN;
                        $user_lv->my_battle_chara = self::MYBATTLECHARA_MIN;

                        $user_lv->my_battle_chara += 1;
                        $user->user_lv += 1;
                        $user->save();
                        $user_lv->save();

                        $default_chara = new BattleChara(8); //默认角色小豆泥(id=8)
                        MyBattleChara::create(
                            [
                                'user_id' => $user->id,
                                'chara_id' => 0, //chara_id从0开始，这里要-1
                                'name' => $default_chara->CharaName(),
                                'heads' => $default_chara->CharaHeadsAll(),
                                'messages' => $default_chara->CharaAttackMessagesAll(),
                            ]
                        );
                        Log::channel('temp_log')->info('new_lv', [$user->id]);
                    } elseif ($user_lv->my_battle_chara == 0) {
                        
                        $user_lv->my_battle_chara += 1;
                        $user->user_lv += 1;
                        $user->save();
                        $user_lv->save();

                        $default_chara = new BattleChara(8); //默认角色小豆泥(id=8)
                        MyBattleChara::create(
                            [
                                'user_id' => $user->id,
                                'chara_id' => 0, //chara_id从0开始，这里要-1
                                'name' => $default_chara->CharaName(),
                                'heads' => $default_chara->CharaHeadsAll(),
                                'messages' => $default_chara->CharaAttackMessagesAll(),
                            ]
                        );
                        Log::channel('temp_log')->info('old_lv+1', [$user->id]);
                    }
                }

                $bar->advance();
            }
            sleep(0.1);
        });

        $bar->finish();
        return True;
    }
}
