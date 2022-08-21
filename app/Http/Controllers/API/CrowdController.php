<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Crowd;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Carbon;
use App\Common\ResponseCode;
use App\Models\CrowdRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Exceptions\CoinException;
use App\Jobs\ProcessUserActive;
use App\Jobs\ProcessCrowdRepeal;

class CrowdController extends Controller
{
    public function show(Request $request, $crowd_id)
    {
        $crowd = Crowd::find($crowd_id);
        if (!$crowd) {
            return response()->json([
                'code' => ResponseCode::CROWD_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::CROWD_NOT_FOUND],
            ]);
        }

        $user = User::where('binggan', $request->query('binggan'))->first();

        if ($user) {
            $crowd_record = $crowd->CrowdUserRecord($user->id);
        } else {
            $crowd_record = [];
        }

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'crowd' => $crowd,
            'crowd_record' => $crowd_record,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string|max:100',
            'crowd_id' => 'integer|required',
            'crowd_olo' => 'integer|required|max:1000000|min:1',
        ]);

        $user = $request->user;

        try {
            DB::beginTransaction();
            $crowd = Crowd::lockForUpdate()->find($request->crowd_id); //悲观锁避免超额
            //检查众筹id是否存在
            if (!$crowd) {
                DB::rollback();
                return response()->json([
                    'code' => ResponseCode::CROWD_NOT_FOUND,
                    'message' => ResponseCode::$codeMap[ResponseCode::CROWD_NOT_FOUND],
                ]);
            }
            //检查众筹是否过期
            if ($crowd->end_date < Carbon::now()) {
                DB::rollback();
                return response()->json([
                    'code' => ResponseCode::CROWD_WAS_OUTDATE,
                    'message' => ResponseCode::$codeMap[ResponseCode::CROWD_WAS_OUTDATE],
                ]);
            }
            //检查众筹是否结束
            if ($crowd->is_closed != 0) {
                DB::rollback();
                return response()->json([
                    'code' => ResponseCode::CROWD_HAS_BEEN_CLOSED,
                    'message' => ResponseCode::$codeMap[ResponseCode::CROWD_HAS_BEEN_CLOSED],
                ]);
            }


            $crowd_remain = $crowd->olo_target - $crowd->olo_total;
            $olo_to_crowd = $request->crowd_olo;
            $crowd_too_much = false; //超额众筹
            if ($olo_to_crowd >= $crowd_remain) {
                $olo_to_crowd = $crowd_remain;
                $crowd->is_closed = 1; //达成，关闭众筹
                $crowd_too_much = true;
            }

            //该众筹事件增加olo总额
            $crowd->olo_total += $olo_to_crowd;
            $crowd->save();

            //追加众筹记录流程
            $crowd_record = new CrowdRecord;
            $crowd_record->user_id = $user->id;
            $crowd_record->crowd_id = $request->crowd_id;
            $crowd_record->olo = $olo_to_crowd;
            $crowd_record->save();

            //执行新回复流程
            $thread_id = $crowd->thread_id;
            $post_content = sprintf("我众筹了%u块奥利奥", $olo_to_crowd);
            $post = new Post;
            $post->setSuffix(intval($thread_id / 10000));
            $post->created_binggan = $request->binggan;
            $post->forum_id = $crowd->Thread->Forum->id;
            $post->thread_id = $thread_id;
            $post->content = $post_content;
            $post->nickname = '众筹系统';
            $post->created_by_admin = 2;
            $post->created_ip = $request->ip();
            $post->random_head = random_int(0, 39);
            $thread = $post->thread;
            $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
            $post->floor = $thread->posts_num;
            $thread->save();
            $post->save();

            //扣除用户相应olo
            // $user->coinConsume($olo_to_crowd);
            $user->coinChange(
                'normal', //记录类型
                [
                    'olo' => -$olo_to_crowd,
                    'content' => '参与众筹',
                    'thread_id' => $thread->id,
                    'thread_title' => $thread->title,
                    'post_id' => $post->id,
                    'floor' => $post->floor,
                ]
            ); //扣除用户相应olo（通过统一接口、记录操作）
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
                'active' => '用户参加了众筹',
                'content' => 'olo:' . $olo_to_crowd . ' crowd_id:' . $crowd->id,
            ]
        );

        if ($crowd_too_much) {
            return response()->json(
                [
                    'code' => ResponseCode::CROWD_TOO_MANY,
                    'message' => '由于参与额已超过目标，多余olo已退回。实际参与额为：' . $olo_to_crowd,
                    'data' => [
                        'crowd_id' => $request->crowd_id,
                    ]
                ],
            );
        }

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '参与众筹成功！',
                'data' => [
                    'crowd_id' => $request->crowd_id,
                ]
            ],
        );
    }

    public function repeal(Request $request)
    {
        $request->validate([
            'crowd_id' => 'integer|required',
        ]);

        $user = $request->user();

        $crowd = Crowd::find($request->crowd_id);
        //检查投票id是否存在
        if (!$crowd) {
            return response()->json([
                'code' => ResponseCode::CROWD_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::CROWD_NOT_FOUND],
            ]);
        }

        //检查管理员权限
        if (!in_array($user->admin, [99, 10])) {
            return response()->json(
                [
                    'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                    'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
                ],
            );
        }

        //检查菠菜是否已关闭
        if ($crowd->is_closed != 0) {
            return response()->json([
                'code' => ResponseCode::CROWD_HAS_BEEN_CLOSED,
                'message' => ResponseCode::$codeMap[ResponseCode::CROWD_HAS_BEEN_CLOSED],
            ]);
        }
        try {
            DB::beginTransaction();
            if (Carbon::parse($crowd->end_date) > Carbon::now()) {
                $crowd->end_date = Carbon::now();
            }
            $crowd->is_closed = 2; //0=进行中；1=已正常结束；2=已中止
            $crowd->save();

            //执行新回复流程
            $thread_id = $crowd->Thread->id;
            $post_content = sprintf("管理员已中止此众筹，众筹的olo已退回。");
            $post = new Post;
            $post->setSuffix(intval($thread_id / 10000));
            $post->created_binggan = $request->binggan;
            $post->forum_id = $crowd->Thread->Forum->id;
            $post->thread_id = $thread_id;
            $post->content = $post_content;
            $post->nickname = '众筹系统';
            $post->created_by_admin = 2;
            $post->created_ip = $request->ip();
            $post->random_head = random_int(0, 39);
            $thread = $post->thread;
            $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
            $post->floor = $thread->posts_num;
            $thread->save();
            $post->save();
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试',
            ]);
        }
        //中止菠菜，所有olo原路返回
        ProcessCrowdRepeal::dispatch(
            [
                'crowd_id' => $request->crowd_id,
            ]
        );


        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员中止了菠菜',
                'content' => 'crowd_id:' . $request->crowd_id,
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '已中止该菠菜',
                'data' => [
                    'gamble_question_id' => $request->gamble_question_id,
                ]
            ],
        );
    }
}
