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
        $request->validate([
            'binggan' => 'required|string',
            'forum_id' => 'required|integer',
            'thread_id' => 'required|integer',
            'battle_olo' => 'required|integer|max:100000|min:1',
            'chara_id' => 'required|integer',
        ]);

        $user = $request->user;

        $water_check = $user->waterCheck('new_post');
        if ($water_check != 'ok') return $water_check;


        $can_battle = DB::table('threads')->where('id', $request->thread_id)->value('can_battle');
        if ($can_battle == 0) {
            return response()->json([
                'code' => ResponseCode::BATTLE_CANNOT_CREATED,
                'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_CANNOT_CREATED],
            ]);
        }

        try {
            DB::beginTransaction();

            $post = new Post;
            $post->setSuffix(intval($request->thread_id / 10000));
            $post->created_binggan = $request->binggan;
            $post->forum_id = $request->forum_id;
            $post->thread_id = $request->thread_id;
            $post->content = "我发起了一场表情包大乱斗！";
            $post->nickname = '大乱斗系统';
            $post->created_by_admin = 2; //0=一般用户 1=管理员发布，2=系统发布
            $post->created_ip = $request->ip();
            $post->random_head = random_int(1, 40);
            $thread = $post->thread;
            $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
            $post->floor = $thread->posts_num;
            $post->save();
            $thread->save();

            $battle = new Battle;
            $battle->created_binggan = $user->binggan;
            $battle->initiator_user_id = $user->id;
            $battle->initiator_chara = $request->chara_id;
            $battle->battle_olo = $request->battle_olo;
            $battle->save();

            $post->battle_id = $battle->id; //记得给post加上battle_id
            $post->save();

            $battle_message = new BattleMessage;
            $battle_message->battle_id = $battle->id;
            $battle_message->chara_url = BattleChara::CharaHead($request->chara_id, 'wait');
            $battle_message->message_type = 1;
            $battle_message->message = BattleChara::CharaName($request->chara_id) . "正在等待挑战者……押注：" . $request->battle_olo . "个奥利奥";
            $battle_message->save();

            $user->coinConsume($request->battle_olo);

            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试',
            ]);
        } catch (CoinException $e) {
            DB::rollback();
            return response()->json(
                [
                    'code' => ResponseCode::COIN_NOT_ENOUGH,
                    'message' => ResponseCode::$codeMap[ResponseCode::COIN_NOT_ENOUGH],
                ],
            );
        }

        $user->waterRecord('new_post'); //用redis记录发帖频率。

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户发起 了大乱斗',
                'post_id' => $request->post_id,
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
        ]);

        $user = $request->user;

        // $battle = Battle::find($request->battle_id);
        $battle = DB::table('battles')->where('id', $request->battle_id)->lockForUpdate()->first(); //改用悲观锁
        if (!$battle) {
            return response()->json([
                'code' => ResponseCode::BATTLE_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_NOT_FOUND],
            ]);
        }
        //确认大乱斗是否已经有挑战者参加了
        if ($battle->progress == 1) {
            return response()->json([
                'code' => ResponseCode::BATTLE_HAS_BEEN_ROLL_C,
                'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_HAS_BEEN_ROLL_C],
            ]);
        }
        //确认大乱斗是否已经正常结束了
        if ($battle->progress == 2) {
            return response()->json([
                'code' => ResponseCode::BATTLE_HAVE_BEEN_BET,
                'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_HAVE_BEEN_BET],
            ]);
        }
        //确认大乱斗是否已经过期结束了
        if ($battle->progress  == 3) {
            return response()->json([
                'code' => ResponseCode::BATTLE_WAS_OUTDATE,
                'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_WAS_OUTDATE],
            ]);
        }


        $rand_num = BattleChara::CharaRandNum($request->chara_id);

        try {
            DB::beginTransaction();

            // $battle->progress = 1;
            // $battle->challenger_binggan = $request->binggan;
            // $battle->challenger_user_id = $user->id;
            // $battle->challenger_chara = $request->chara_id;
            // $battle->challenger_rand_num = $rand_num;
            // $battle->save();

            //用了悲观锁，就不能用eloquent更新了？好像。
            DB::table('battles')->where('id', $request->battle_id)->update(
                [
                    'progress' => 1,
                    'challenger_binggan' => $request->binggan,
                    'challenger_user_id' => $user->id,
                    'challenger_chara' => $request->chara_id,
                    'challenger_rand_num' => $rand_num,
                ]
            );

            //发起挑战的动作
            $battle_message = new BattleMessage;
            $battle_message->battle_id = $battle->id;
            $battle_message->chara_url = BattleChara::CharaHead($request->chara_id, 'against');
            $battle_message->message_type = 2;
            $battle_message->message = BattleChara::CharaName($request->chara_id) . '前来挑战！';
            $battle_message->save();

            //发起攻击（投色子）的动作
            $battle_message = new BattleMessage;
            $battle_message->battle_id = $battle->id;
            $battle_message->chara_url = BattleChara::CharaHead($request->chara_id, 'attack');
            $battle_message->message_type = 2;
            $battle_message->message = BattleChara::CharaAttackMessage($request->chara_id, $rand_num);
            $battle_message->save();


            //等待迎战的系统公告
            $battle_message = new BattleMessage;
            $battle_message->battle_id = $battle->id;
            $battle_message->message_type = 0;
            $battle_message->message = '等待发起者迎战';
            $battle_message->save();

            $user->coinConsume($battle->battle_olo);

            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试',
            ]);
        } catch (CoinException $e) {
            DB::rollback();
            return response()->json(
                [
                    'code' => ResponseCode::COIN_NOT_ENOUGH,
                    'message' => ResponseCode::$codeMap[ResponseCode::COIN_NOT_ENOUGH],
                ],
            );
        }


        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户参加了大乱斗（挑战者）',
                'content' => '投出的点数：' . $rand_num,
                'post_id' => $request->post_id,
            ]
        );


        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '已投出色子！',
        ]);
    }

    public function initiator_roll(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'battle_id' => 'required|integer',
        ]);

        $user = $request->user;

        $battle = Battle::find($request->battle_id);
        if (!$battle) {
            return response()->json([
                'code' => ResponseCode::BATTLE_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_NOT_FOUND],
            ]);
        }
        //确认大乱斗是否还在等待参加者
        if ($battle->progress == 0) {
            return response()->json([
                'code' => ResponseCode::BATTLE_HAS_BEEN_ROLL_I,
                'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_HAS_BEEN_ROLL_I],
            ]);
        }
        //确认大乱斗是否已经正常结束了
        if ($battle->progress == 2) {
            return response()->json([
                'code' => ResponseCode::BATTLE_HAVE_BEEN_BET,
                'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_HAVE_BEEN_BET],
            ]);
        }
        //确认大乱斗是否已经过期结束了
        if ($battle->progress  == 3) {
            return response()->json([
                'code' => ResponseCode::BATTLE_WAS_OUTDATE,
                'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_WAS_OUTDATE],
            ]);
        }

        // $initiator_user = User::find($battle->initiator_user_id);
        $initiator_user = $user;
        $challenger_user = User::find($battle->challenger_user_id);
        $rand_num = BattleChara::CharaRandNum($battle->initiator_chara);

        try {
            DB::beginTransaction();

            //发起攻击（投色子）的动作
            $battle_message = new BattleMessage;
            $battle_message->battle_id = $battle->id;
            $battle_message->chara_url = BattleChara::CharaHead($battle->initiator_chara, 'attack');
            $battle_message->message_type = 1;
            $battle_message->message = BattleChara::CharaAttackMessage($battle->initiator_chara, $rand_num);
            $battle_message->save();


            $battle->progress = 2;
            $battle->initiator_rand_num = $rand_num;
            $difference = intval($battle->initiator_rand_num - $battle->challenger_rand_num);
            switch ($difference) {
                case 0: { //平局！
                        $battle->result = 3;
                        $challenger_user->coin += intval($battle->battle_olo * 1.96);
                        $initiator_user->coin += intval($battle->battle_olo * 1.96);
                        $challenger_user->save();
                        $initiator_user->save();

                        //平局系统公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->message_type = 0;
                        $battle_message->message = BattleChara::CharaName($battle->challenger_chara) . '和' . BattleChara::CharaName($battle->initiator_chara)
                            . '的脑门闪过一道光，他们相互理解了！';
                        $battle_message->save();


                        //平局发起者公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->chara_url = BattleChara::CharaHead($battle->initiator_chara, 'win');
                        $battle_message->message_type = 1;
                        $battle_message->message = BattleChara::CharaName($battle->initiator_chara) .
                            '获得奖金' .
                            intval($battle->battle_olo * 1.96) . '个奥利奥';
                        $battle_message->save();

                        //平局挑战者公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->chara_url = BattleChara::CharaHead($battle->challenger_chara, 'win');
                        $battle_message->message_type = 2;
                        $battle_message->message = BattleChara::CharaName($battle->challenger_chara) .
                            '获得奖金' .
                            intval($battle->battle_olo * 1.96) . '个奥利奥';
                        $battle_message->save();
                        break;
                    }
                case $difference > 0: { //发起者胜利
                        $battle->result = 1;
                        $initiator_user->coin += intval($battle->battle_olo * 1.96);
                        $initiator_user->save();

                        //胜利者公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->chara_url = BattleChara::CharaHead($battle->initiator_chara, 'win');
                        $battle_message->message_type = 1;
                        $battle_message->message = BattleChara::CharaName($battle->initiator_chara) .
                            '胜利！获得奖金' .
                            intval($battle->battle_olo * 1.96) . '个奥利奥';
                        $battle_message->save();

                        //失败者公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->chara_url = BattleChara::CharaHead($battle->challenger_chara, 'lose');
                        $battle_message->message_type = 2;
                        $battle_message->message = BattleChara::CharaName($battle->challenger_chara) .
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
                        $battle->result = 2;
                        $challenger_user->coin += intval($battle->battle_olo * 1.96);
                        $challenger_user->save();

                        //胜利者公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->chara_url = BattleChara::CharaHead($battle->challenger_chara, 'win');
                        $battle_message->message_type = 2;
                        $battle_message->message = BattleChara::CharaName($battle->challenger_chara) .
                            '胜利！获得奖金' .
                            intval($battle->battle_olo * 1.96) . '个奥利奥';
                        $battle_message->save();

                        //失败者公告
                        $battle_message = new BattleMessage;
                        $battle_message->battle_id = $battle->id;
                        $battle_message->chara_url = BattleChara::CharaHead($battle->initiator_chara, 'lose');
                        $battle_message->message_type = 1;
                        $battle_message->message = BattleChara::CharaName($battle->initiator_chara) .
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
            $battle->save();

            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试',
            ]);
        } catch (CoinException $e) {
            DB::rollback();
            return response()->json(
                [
                    'code' => ResponseCode::COIN_NOT_ENOUGH,
                    'message' => ResponseCode::$codeMap[ResponseCode::COIN_NOT_ENOUGH],
                ],
            );
        }


        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户参加了大乱斗（发起者）',
                'content' => '投出的点数：' . $rand_num . '结果是：' . $battle->result,
                'post_id' => $request->post_id,
            ]
        );


        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => '已结束大乱斗！',
            'd' => $difference,
        ]);
    }
}
