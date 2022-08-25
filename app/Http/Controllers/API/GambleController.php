<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Exceptions\CoinException;
use Illuminate\Support\Facades\DB;
use App\Common\ResponseCode;
use App\Jobs\ProcessGambleClose;
use App\Jobs\ProcessGambleRepeal;
use App\Jobs\ProcessUserActive;
use App\Models\User;
use App\Models\Post;
use App\Models\GambleUser;
use App\Models\GambleOption;
use App\Models\GambleQuestion;
use Carbon\Carbon;
use Exception;

class GambleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string|max:100',
            'gamble_question_id' => 'integer|required',
            'bet_option' => 'integer|required',
            'betting_olo' => 'required|integer|max:1000000|min:1',
        ]);

        $user = $request->user;

        $gamble_question = GambleQuestion::find($request->gamble_question_id);
        //检查投票id是否存在
        if (!$gamble_question) {
            return response()->json([
                'code' => ResponseCode::GAMBLE_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::GAMBLE_NOT_FOUND],
            ]);
        }

        //检查投票是否过期
        if (Carbon::parse($gamble_question->end_date) < Carbon::now()) {
            return response()->json([
                'code' => ResponseCode::GAMBLE_WAS_OUTDATE,
                'message' => ResponseCode::$codeMap[ResponseCode::GAMBLE_WAS_OUTDATE],
            ]);
        }

        //检查菠菜是否已关闭
        if ($gamble_question->is_closed != 0) {
            return response()->json([
                'code' => ResponseCode::GAMBLE_HAS_BEEN_CLOSED,
                'message' => ResponseCode::$codeMap[ResponseCode::GAMBLE_HAS_BEEN_CLOSED],
            ]);
        }

        $bet_option = GambleOption::find($request->bet_option);
        //检查投票选项id是否存在
        if (!$bet_option) {
            return response()->json([
                'code' => ResponseCode::GAMBLE_OPTION_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::GAMBLE_OPTION_NOT_FOUND],
                '$bet_option' => $bet_option,
                '$request->bet_option' => $request->bet_option,
            ]);
        }

        try {
            DB::beginTransaction();
            //该菠菜事件增加投注数
            $gamble_question->bet_total++;
            $gamble_question->olo_total += $request->betting_olo;;
            $gamble_question->save();

            //该下注选项增加投注数
            $bet_option->bet_total++;
            $bet_option->olo_total += $request->betting_olo;

            $bet_option->save();

            //刷新所有选项的赔率
            foreach ($gamble_question->GambleOptions as $option) {
                if ($option->olo_total == 0) {
                    $option->odds = 93; //如果该选项还没被投注，则赔率设定上限93。（本质无意义，仅显示用）
                } else {
                    $option->odds = round($gamble_question->olo_total / $option->olo_total * 0.93, 2, PHP_ROUND_HALF_DOWN);
                }
                $option->save();
            }

            //追加下注记录流程
            $gamble_user = new GambleUser;
            $gamble_user->setSuffix(intval($request->gamble_question_id / 10000));
            $gamble_user->user_id = $user->id;
            $gamble_user->gamble_question_id = $request->gamble_question_id;
            $gamble_user->option_id = $request->bet_option;
            $gamble_user->betting_olo = $request->betting_olo;
            $gamble_user->save();

            //执行新回复流程
            $thread_id = $gamble_question->Thread->id;
            $post_content = sprintf("我为“%s”下注了%u块奥利奥", $bet_option->option_text, $request->betting_olo); //TODO
            $post = new Post;
            $post->setSuffix(intval($thread_id / 10000));
            $post->created_binggan = $request->binggan;
            $post->forum_id = $gamble_question->Thread->Forum->id;
            $post->thread_id = $thread_id;
            $post->content = $post_content;
            $post->nickname = '菠菜系统';
            $post->created_by_admin = 2;
            $post->created_ip = $request->ip();
            $post->random_head = random_int(0, 39);
            $thread = $post->thread;
            $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
            $post->floor = $thread->posts_num;

            //扣除用户相应olo
            // $user->coinConsume($request->betting_olo);
            $user->coinChange(
                'normal', //记录类型
                [
                    'olo' => -$request->betting_olo,
                    'content' => '菠菜投注',
                    'thread_id' => $thread->id,
                    'thread_title' => $thread->title,
                    'post_id' => $post->id,
                    'floor' => $post->floor,
                ]
            ); //扣除用户相应olo（通过统一接口、记录操作）

            $thread->save();
            $post->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户参加了菠菜',
                'gamble_question_id' => $request->gamble_question_id,
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '下注成功！',
                'data' => [
                    'gamble_question_id' => $request->gamble_question_id,
                    'gamble_option_id' => $request->bet_option,
                ]
            ],
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $gamble_question_id)
    {
        $gamble_question = GambleQuestion::find($gamble_question_id);
        if (!$gamble_question) {
            return response()->json([
                'code' => ResponseCode::GAMBLE_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::GAMBLE_NOT_FOUND],
            ]);
        }
        $gamble_options  = $gamble_question->GambleOptions;


        $user = User::where('binggan', $request->query('binggan'))->first();

        if ($user) {
            $user_choices = $gamble_question->GambleUserChoices($user->id);
        } else {
            $user_choices = [];
        }

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'gamble_question' => $gamble_question,
            'gamble_options' => $gamble_options,
            'user_choices' => $user_choices,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function close(Request $request)
    {
        $request->validate([
            'gamble_question_id' => 'integer|required',
            'result_option' => 'integer|required',
        ]);

        $user = $request->user();

        $gamble_question = GambleQuestion::find($request->gamble_question_id);
        //检查菠菜id是否存在
        if (!$gamble_question) {
            return response()->json([
                'code' => ResponseCode::GAMBLE_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::GAMBLE_NOT_FOUND],
            ]);
        }

        //检查菠菜是否已关闭
        if ($gamble_question->is_closed != 0) {
            return response()->json([
                'code' => ResponseCode::GAMBLE_HAS_BEEN_CLOSED,
                'message' => ResponseCode::$codeMap[ResponseCode::GAMBLE_HAS_BEEN_CLOSED],
            ]);
        }

        $result_option = GambleOption::find($request->result_option);
        //检查菠菜选项id是否存在
        if (!$result_option) {
            return response()->json([
                'code' => ResponseCode::GAMBLE_OPTION_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::GAMBLE_OPTION_NOT_FOUND],
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

        try {
            DB::beginTransaction();
            if (Carbon::parse($gamble_question->end_date) > Carbon::now()) {
                $gamble_question->end_date = Carbon::now();
            }
            $gamble_question->result_option_id = $request->result_option;
            $gamble_question->is_closed = 1; //0=进行中；1=已正常结束；2=已中止
            $gamble_question->save();

            //执行新回复流程
            $thread_id = $gamble_question->Thread->id;
            $post_content = sprintf("管理员已为此菠菜开奖，中奖项为：%s <br>奖金已发放，恭喜中奖的各位！", $result_option->option_text);
            $post = new Post;
            $post->setSuffix(intval($thread_id / 10000));
            $post->created_binggan = $request->binggan;
            $post->forum_id = $gamble_question->Thread->Forum->id;
            $post->thread_id = $thread_id;
            $post->content = $post_content;
            $post->nickname = '菠菜系统';
            $post->created_by_admin = 2;
            $post->created_ip = $request->ip();
            $post->random_head = random_int(0, 39);
            $thread = $post->thread;
            $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
            $post->floor = $thread->posts_num;
            $thread->save();
            $post->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        //发放所有投票结果
        ProcessGambleClose::dispatch(
            [
                'gamble_question_id' => $request->gamble_question_id,
                'result_option' => $result_option,
            ]
        );

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员结束了菠菜，结果是' . $request->result_option,
                'gamble_question_id' => $request->gamble_question_id,
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '已结束该菠菜',
                'data' => [
                    'gamble_question_id' => $request->gamble_question_id,
                    'result_option' => $request->result_option,
                ]
            ],
        );
    }

    public function repeal(Request $request)
    {
        $request->validate([
            'gamble_question_id' => 'integer|required',
        ]);

        $user = $request->user();

        $gamble_question = GambleQuestion::find($request->gamble_question_id);
        //检查投票id是否存在
        if (!$gamble_question) {
            return response()->json([
                'code' => ResponseCode::GAMBLE_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::GAMBLE_NOT_FOUND],
            ]);
        }

        //检查菠菜是否已关闭
        if ($gamble_question->is_closed != 0) {
            return response()->json([
                'code' => ResponseCode::GAMBLE_HAS_BEEN_CLOSED,
                'message' => ResponseCode::$codeMap[ResponseCode::GAMBLE_HAS_BEEN_CLOSED],
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

        try {
            DB::beginTransaction();
            if (Carbon::parse($gamble_question->end_date) > Carbon::now()) {
                $gamble_question->end_date = Carbon::now();
            }
            $gamble_question->result_option_id = $request->result_option;
            $gamble_question->is_closed = 2; //0=进行中；1=已正常结束；2=已中止
            $gamble_question->save();

            //执行新回复流程
            $thread_id = $gamble_question->Thread->id;
            $post_content = sprintf("管理员已中止此菠菜，投注额已退回。");
            $post = new Post;
            $post->setSuffix(intval($thread_id / 10000));
            $post->created_binggan = $request->binggan;
            $post->forum_id = $gamble_question->Thread->Forum->id;
            $post->thread_id = $thread_id;
            $post->content = $post_content;
            $post->nickname = '菠菜系统';
            $post->created_by_admin = 2;
            $post->created_ip = $request->ip();
            $post->random_head = random_int(0, 39);
            $thread = $post->thread;
            $thread->posts_num = POST::Suffix(intval($thread->id / 10000))->where('thread_id', $thread->id)->count();
            $post->floor = $thread->posts_num;
            $thread->save();
            $post->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        //中止菠菜，所有olo原路返回
        ProcessGambleRepeal::dispatch(
            [
                'gamble_question_id' => $request->gamble_question_id,
            ]
        );


        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员中止了菠菜',
                'gamble_question_id' => $request->gamble_question_id,
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
