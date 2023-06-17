<?php

namespace App\Http\Controllers\API;

use App\Common\BattleChara;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Battle;
use App\Models\BattleMessage;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use App\Exceptions\CoinException;
use Illuminate\Support\Facades\DB;
use App\Common\ResponseCode;
use App\Jobs\ProcessUserActive;
use App\Jobs\ProcessIncomeStatement;
use App\Models\Thread;
use Illuminate\Support\Carbon;
use App\Events\NewPostBroadcast;
use App\Models\UserMedalRecord;
use Exception;

class BattleController extends Controller
{
    public function show(Request $request, $battle_id)
    {
        $battle = Battle::find($battle_id);
        if (!$battle) {
            return response()->json([
                'code' => ResponseCode::BATTLE_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_NOT_FOUND],
            ]);
        }
        $battle_messages  = $battle->BattleMessages;

        //如果有提供binggan，为battle输入binggan，用来判断is_your_battle（为前端提供是否是用户自己帖子的判据）
        if ($request->query('binggan')) {
            $battle->setBinggan($request->query('binggan'));
        }
        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'battle' => $battle,
            'battle_messages' => $battle_messages,
        ]);
    }

    public function create(Request $request)
    {
        if (Carbon::now()->between('2022/12/6 08:00:00', '2022/12/7 00:00:00')) {
            return response()->json([
                'code' => ResponseCode::DEFAULT,
                'message' => '今天8:00到24:00暂停大乱斗和红包。T_T',
            ]);
        }

        $request->validate([
            'binggan' => 'required|string',
            'forum_id' => 'required|integer',
            'thread_id' => 'required|integer',
            'battle_olo' => 'required|integer|max:1000000|min:1',
            'chara_id' => 'required|integer',
            'is_custom_chara' => 'nullable|boolean',
            'chara_group' => 'required|integer',
            'new_post_key' => 'string',
            'timestamp' => 'integer',
        ]);

        $user = $request->user;

        $water_check = $user->waterCheck('new_post', $request->ip(), $request->thread_id, $request);
        if ($water_check != 'ok') return $water_check;


        $can_battle = DB::table('threads')->where('id', $request->thread_id)->value('can_battle');
        if ($can_battle == 0) {
            return response()->json([
                'code' => ResponseCode::BATTLE_CANNOT_CREATED,
                'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_CANNOT_CREATED],
            ]);
        }

        if (!$request->is_custom_chara) {
            $battle_chara = new BattleChara($request->chara_id, null); //这里不给出第二个参数$user_id，就不使用自定义大乱斗角色
        } else {
            $battle_chara = new BattleChara($request->chara_id, $user->id); //这里给出第二个参数$user_id，使用自定义大乱斗角色
        }



        try {
            DB::beginTransaction();

            // $post = new Post;
            // $post->setSuffix(intval($request->thread_id / 10000));
            // $post->created_binggan = $request->binggan;
            // $post->forum_id = $request->forum_id;
            // $post->thread_id = $request->thread_id;
            // $post->content = "我发起了一场表情包大乱斗！";
            // $post->nickname = '大乱斗系统';
            // $post->created_by_admin = 2; //0=一般用户 1=管理员发布，2=系统发布
            // $post->created_ip = $request->ip();
            // $post->random_head = random_int(0, 39);
            // $thread = $post->thread;
            // $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
            // $post->floor = $thread->posts_num;
            // $post->save();
            // $thread->save();

            $post = Post::create([
                'created_binggan' => $request->binggan,
                'forum_id' => $request->forum_id,
                'thread_id' => $request->thread_id,
                'content' => "我发起了一场表情包大乱斗！",
                'nickname' => '大乱斗系统',
                'created_by_admin' => 2,
                'created_IP' => $request->ip(),
            ]);


            $battle = new Battle;
            $battle->thread_id = $request->thread_id;
            $battle->post_id = $post->id;
            $battle->created_binggan = $user->binggan;
            $battle->initiator_user_id = $user->id;
            $battle->initiator_chara = $request->chara_id;
            $battle->initiator_is_custom_chara = $request->is_custom_chara;
            $battle->battle_olo = $request->battle_olo;
            $battle->chara_group = $request->chara_group;
            $battle->save();

            $post->battle_id = $battle->id; //记得给post加上battle_id
            $post->save();

            $battle_message = new BattleMessage;
            $battle_message->battle_id = $battle->id;
            $battle_message->chara_url = $battle_chara->CharaHead('wait');
            $battle_message->message_type = 1;
            $battle_message->message = $battle_chara->CharaName() . "正在等待挑战者……押注：" . $request->battle_olo . "个奥利奥";
            $battle_message->save();

            $user->coinConsume($request->battle_olo);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        $user->waterRecord('new_post', $request->ip()); //用redis记录发帖频率。

        //广播发帖动作
        // broadcast(new NewPostBroadcast($request->thread_id, $post->id, $post->floor))->toOthers();
        $post->broadcast();

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户发起 了大乱斗',
                'thread_id' => $request->thread_id,
                'post_id' => $post->id,
            ]
        );


        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '已发起大乱斗！',
        ]);
    }

    public function challenger_roll(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'battle_id' => 'required|integer',
            'chara_id' => 'required|integer',
            'is_custom_chara' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $battle = DB::table('battles')->where('id', $request->battle_id)->lockForUpdate()->first(); //改用悲观锁
            if (!$battle) {
                DB::rollback();
                return response()->json([
                    'code' => ResponseCode::BATTLE_NOT_FOUND,
                    'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_NOT_FOUND],
                ]);
            }
            //确认大乱斗是否已经有挑战者参加了
            if ($battle->progress == 1) {
                DB::rollback();
                return response()->json([
                    'code' => ResponseCode::BATTLE_HAS_BEEN_ROLL_C,
                    'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_HAS_BEEN_ROLL_C],
                ]);
            }
            //确认大乱斗是否已经正常结束了
            if ($battle->progress == 2) {
                DB::rollback();
                return response()->json([
                    'code' => ResponseCode::BATTLE_HAVE_BEEN_BET,
                    'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_HAVE_BEEN_BET],
                ]);
            }
            //确认大乱斗是否已经过期结束了
            if ($battle->progress  == 3) {
                DB::rollback();
                return response()->json([
                    'code' => ResponseCode::BATTLE_WAS_OUTDATE,
                    'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_WAS_OUTDATE],
                ]);
            }

            $initiator_user = User::find($battle->initiator_user_id);
            $challenger_user = $request->user;

            //创建角色BattleChara类
            if (!$battle->initiator_is_custom_chara) {
                $initiator_chara = new BattleChara(
                    $battle->initiator_chara,
                    null, //这里不给出第二个参数$user_id，就不使用自定义大乱斗角色
                    $request->chara_id, //对手角色id
                    $request->is_custom_chara ? $challenger_user->id : null //如果is_custom_chara，挑战者使用自定义角色（传入user_id_opponent参数）
                );
            } else {
                $initiator_chara = new BattleChara(
                    $battle->initiator_chara,
                    $initiator_user->id, //这里给出第二个参数$user_id，使用自定义大乱斗角色
                    $request->chara_id, //对手角色id
                    $request->is_custom_chara ? $challenger_user->id : null //如果is_custom_chara，挑战者使用自定义角色（传入user_id_opponent参数）
                );
            }

            if (!$request->is_custom_chara) {
                $challenger_chara = new BattleChara(
                    $request->chara_id,
                    null, //这里不给出第二个参数$user_id，就不使用自定义大乱斗角色
                    $battle->initiator_chara, //对手角色id
                    $battle->initiator_is_custom_chara ? $initiator_user->id : null //如果is_custom_chara，挑战者使用自定义角色（传入user_id_opponent参数）
                );
            } else {
                $challenger_chara = new BattleChara(
                    $request->chara_id,
                    $challenger_user->id, //这里给出第二个参数$user_id，使用自定义大乱斗角色
                    $battle->initiator_chara, //对手角色id
                    $battle->initiator_is_custom_chara ? $initiator_user->id : null //如果is_custom_chara，挑战者使用自定义角色（传入user_id_opponent参数）
                );
            }

            $initiator_rand_num = $initiator_chara->CharaRandNum();
            $challenger_rand_num = $challenger_chara->CharaRandNum();

            $difference = intval($initiator_rand_num - $challenger_rand_num);

            $challenger_user->coinConsume($battle->battle_olo);

            //挑战者发起挑战的动作
            $battle_message = new BattleMessage;
            $battle_message->battle_id = $battle->id;
            $battle_message->chara_url = $challenger_chara->CharaHead('against');
            $battle_message->message_type = 2;
            $battle_message->message = $challenger_chara->CharaName() . '前来挑战！';
            $battle_message->save();

            //挑战者发起攻击（投色子）的动作
            $battle_message = new BattleMessage;
            $battle_message->battle_id = $battle->id;
            $battle_message->chara_url = $challenger_chara->CharaHead('attack');
            $battle_message->message_type = 2;
            $battle_message->message = $challenger_chara->CharaAttackMessage($challenger_rand_num);
            $battle_message->save();

            //发起者发起攻击（投色子）的动作
            $battle_message = new BattleMessage;
            $battle_message->battle_id = $battle->id;
            $battle_message->chara_url = $initiator_chara->CharaHead('attack');
            $battle_message->message_type = 1;
            $battle_message->message = $initiator_chara->CharaAttackMessage($initiator_rand_num);
            $battle_message->save();

            if (Carbon::now()->between('2023/6/18 00:00:00', '2023/6/19 00:00:00')) {
                //618税率2%
                $tax_rate = 1.98;
            } else {
                //税率4%，也就是奖金是1.96倍
                $tax_rate = 1.96;
            }


            switch ($difference) {
                case 0: { //平局！
                        $battle_result = 3;
                        // $challenger_user->coin += intval($battle->battle_olo * $tax_rate);
                        // $initiator_user->coin += intval($battle->battle_olo * $tax_rate);
                        $challenger_user->increment('coin', intval($battle->battle_olo * $tax_rate));
                        $initiator_user->increment('coin', intval($battle->battle_olo * $tax_rate));
                        $challenger_user->save();
                        $initiator_user->save();

                        //平局系统公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->message_type = 0;
                        $battle_message->message = $challenger_chara->CharaName() . '和' . $initiator_chara->CharaName()
                            . '的脑门闪过一道光，他们相互理解了！';
                        $battle_message->save();


                        //平局发起者公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->chara_url = $initiator_chara->CharaHead('win');
                        $battle_message->message_type = 1;
                        $battle_message->message = $initiator_chara->CharaName() .
                            '获得奖金' .
                            intval($battle->battle_olo * $tax_rate) . '个奥利奥';
                        $battle_message->save();

                        //平局挑战者公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->chara_url = $challenger_chara->CharaHead('win');
                        $battle_message->message_type = 2;
                        $battle_message->message = $challenger_chara->CharaName() .
                            '获得奖金' .
                            intval($battle->battle_olo * $tax_rate) . '个奥利奥';
                        $battle_message->save();
                        break;
                    }
                case $difference > 0: { //发起者胜利
                        $battle_result = 1;
                        // $initiator_user->coin += intval($battle->battle_olo * $tax_rate);
                        $initiator_user->increment('coin', intval($battle->battle_olo * $tax_rate));
                        $initiator_user->save();

                        //胜利者公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->chara_url = $initiator_chara->CharaHead('win');
                        $battle_message->message_type = 1;
                        $battle_message->message = $initiator_chara->CharaName() .
                            '胜利！获得奖金' .
                            intval($battle->battle_olo * $tax_rate) . '个奥利奥';
                        $battle_message->save();

                        //失败者公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->chara_url = $challenger_chara->CharaHead('lose');
                        $battle_message->message_type = 2;
                        $battle_message->message = $challenger_chara->CharaName() .
                            '失败了…………哭哭';
                        $battle_message->save();

                        //结果系统公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->message_type = 0;
                        $battle_message->message = '发起者胜利！';
                        $battle_message->save();
                        break;
                    }
                case $difference < 0: { //挑战者胜利
                        $battle_result = 2;
                        // $challenger_user->coin += intval($battle->battle_olo * $tax_rate);
                        $challenger_user->increment('coin', intval($battle->battle_olo * $tax_rate));
                        $challenger_user->save();

                        //胜利者公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->chara_url = $challenger_chara->CharaHead('win');
                        $battle_message->message_type = 2;
                        $battle_message->message = $challenger_chara->CharaName() .
                            '胜利！获得奖金' .
                            intval($battle->battle_olo * $tax_rate) . '个奥利奥';
                        $battle_message->save();

                        //失败者公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->chara_url = $initiator_chara->CharaHead('lose');
                        $battle_message->message_type = 1;
                        $battle_message->message = $initiator_chara->CharaName() .
                            '失败了…………哭哭';
                        $battle_message->save();

                        //结果系统公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->message_type = 0;
                        $battle_message->message = '挑战者胜利！';
                        $battle_message->save();
                        break;
                    }
            }

            //用了悲观锁，就不能用eloquent更新了？好像。
            DB::table('battles')->where('id', $request->battle_id)->update(
                [
                    'progress' => 2,
                    'challenger_binggan' => $challenger_user->binggan,
                    'challenger_user_id' => $challenger_user->id,
                    'challenger_chara' => $request->chara_id,
                    'challenger_rand_num' => $challenger_rand_num,
                    'initiator_rand_num' => $initiator_rand_num,
                    'result' => $battle_result,
                ]
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        switch ($battle_result) {
            case 1:
                $initiator_income_olo = intval($battle->battle_olo * ($tax_rate - 1));
                $initiator_income_content = '大乱斗（胜利）';
                $challenger_income_olo = -$battle->battle_olo;
                $challenger_income_content = '大乱斗（失败）';
                break;
            case 2:
                $initiator_income_olo = -$battle->battle_olo;
                $initiator_income_content = '大乱斗（失败）';
                $challenger_income_olo = intval($battle->battle_olo * ($tax_rate - 1));
                $challenger_income_content = '大乱斗（胜利）';
                break;
            case 3:
                $initiator_income_olo = intval($battle->battle_olo * ($tax_rate - 1));
                $initiator_income_content = '大乱斗（平局）';
                $challenger_income_olo = intval($battle->battle_olo * ($tax_rate - 1));
                $challenger_income_content = '大乱斗（平局）';
                break;
        }

        $thread = Thread::find($battle->thread_id);
        $post = Post::suffix(intval($thread->id / 10000))->find($battle->post_id);

        ProcessUserActive::dispatch(
            [
                'binggan' => $challenger_user->binggan,
                'user_id' => $challenger_user->id,
                'active' => '用户参加了大乱斗（挑战者）',
                'content' => '投出的点数：' . $challenger_rand_num . "。结果：" . $challenger_income_content . "。赌注：" . $challenger_income_olo,
            ]
        );

        ProcessUserActive::dispatch(
            [
                'binggan' => $initiator_user->binggan,
                'user_id' => $initiator_user->id,
                'active' => '用户参加了大乱斗（发起者）',
                'content' => '投出的点数：' . $initiator_rand_num . "。结果：" . $initiator_income_content . "。赌注：" . $initiator_income_olo,
            ]
        );

        //记录olo变动（发起者）
        ProcessIncomeStatement::dispatch(
            'normal', //记录类型
            [
                'created_at' => Carbon::now(),
                'olo' => $initiator_income_olo,
                'user_id' => $initiator_user->id,
                'binggan' => $initiator_user->binggan,
                'user_id_target' => $challenger_user->id,
                'binggan_target' => $challenger_user->binggan,
                'content' => $initiator_income_content,
                'thread_id' => $thread->id,
                'thread_title' => $thread->title,
                'post_id' => $post->id,
                'floor' => $post->floor,
            ]
        );

        //记录olo变动（挑战者）
        ProcessIncomeStatement::dispatch(
            'normal', //记录类型
            [
                'created_at' => Carbon::now(),
                'olo' => $challenger_income_olo,
                'user_id' => $challenger_user->id,
                'binggan' => $challenger_user->binggan,
                'user_id_target' => $initiator_user->id,
                'binggan_target' => $initiator_user->binggan,
                'content' => $challenger_income_content,
                'thread_id' => $thread->id,
                'thread_title' => $thread->title,
                'post_id' => $post->id,
                'floor' => $post->floor,
            ]
        );

        //成就变量查询
        $initiator_medal_record = $initiator_user->UserMedalRecord()->firstOrCreate(); //如果记录不存在就追加
        $challenger_medal_record = $challenger_user->UserMedalRecord()->firstOrCreate(); //如果记录不存在就追加

        //检查成就（发起者olo）
        if ($initiator_income_olo > 0) {
            $initiator_medal_record->push_battle_in($initiator_income_olo);
        } else {
            $initiator_medal_record->push_battle_out($initiator_income_olo);
            $initiator_medal_record->check_olo_0($initiator_user); //有可能olo清零，检查成就
        }
        //检查成就（挑战者olo）
        if ($challenger_income_olo > 0) {
            $challenger_medal_record->push_battle_in($challenger_income_olo);
        } else {
            $challenger_medal_record->push_battle_out($challenger_income_olo);
            $challenger_medal_record->check_olo_0($challenger_user); //有可能olo清零，检查成就
        }
        //检查成就（平局时）
        if ($difference == 0) {
            $challenger_medal_record->check_battle_draw();
            $initiator_medal_record->check_battle_draw();
        }
        //检查成就（点数）
        $challenger_medal_record->check_battle_num($challenger_rand_num);
        $initiator_medal_record->check_battle_num($initiator_rand_num);

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '大乱斗已结束！',
        ]);
    }
}
