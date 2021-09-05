<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Common\ResponseCode;
use App\Jobs\ProcessUserActive;
use App\Models\User;
use App\Models\VoteOption;
use App\Models\VoteQuestion;
use App\Models\VoteUser;
use Carbon\Carbon;

class VoteController extends Controller
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
     * 由ThreadController调用
     * equest检查、事务提交、错误处理等都放在了ThreadController
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $thread_id)
    {
        $request->validate([
            'vote_title' => 'required|string|max:100',
            'vote_end_time' => 'integer|required',
            'vote_options' => 'json|required|max:5000',
            'vote_multiple' => 'boolean|required'
        ]);

        $request_options = json_decode($request->vote_options, true);

        $vote_question = new VoteQuestion;
        $vote_question->thread_id = $thread_id;
        $vote_question->title = $request->vote_title;
        $vote_question->end_date = Carbon::now()->addSeconds($request->vote_end_time);
        $vote_question->multiple = $request->vote_multiple;
        $vote_question->save();

        foreach ($request_options as $request_option) {
            $options = new VoteOption;
            $options->vote_question_id = $vote_question->id;
            $options->option_text = $request_option;
            $options->save();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $vote_question_id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $vote_question_id)
    {
        $vote_question = VoteQuestion::find($vote_question_id);
        if (!$vote_question) {
            return response()->json([
                'code' => ResponseCode::VOTE_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::VOTE_NOT_FOUND],
            ]);
        }
        $vote_options  = $vote_question->VoteOption;


        $user = User::where('binggan', $request->query('binggan'))->first();
        $user_choices = "";
        if ($user) {
            $user_choices = $vote_question->VoteUserChoices($user->id);
        }

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'vote_question' => $vote_question,
            'vote_options' => $vote_options,
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

    public function vote_create(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string|max:100',
            'vote_question_id' => 'integer|required',
            'vote_options_id' => 'json|required|max:500',
        ]);

        $user = User::where('binggan', $request->binggan)->first();
        //确认用户是否存在
        if (!$user) {
            return response()->json(
                [
                    'code' => ResponseCode::USER_NOT_FOUND,
                    'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
                ],
            );
        }

        //如果饼干被ban，直接返回错误
        if ($user->is_banned) {
            return response()->json(
                [
                    'code' => ResponseCode::USER_BANNED,
                    'message' => ResponseCode::$codeMap[ResponseCode::USER_BANNED],
                    'data' => [
                        'binggan' => $user->binggan,
                    ],
                ],
                401
            );
        }

        //查询饼干是否在封禁期
        if ($user->lockedTTL) {
            $lockTTL_hours = intval($user->lockedTTL / 3600) + 1;
            return response()->json(
                [
                    'code' => ResponseCode::USER_LOCKED,
                    'message' => ResponseCode::$codeMap[ResponseCode::USER_LOCKED] . '，将于' . $lockTTL_hours . '小时后解封',
                ],
            );
        }

        $vote_question = VoteQuestion::find($request->vote_question_id);
        //检查投票id是否存在
        if (!$vote_question) {
            return response()->json([
                'code' => ResponseCode::VOTE_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::VOTE_NOT_FOUND],
            ]);
        }
        //检查投票是否过期
        if ($vote_question->end_date < Carbon::now()) {
            return response()->json([
                'code' => ResponseCode::VOTE_WAS_OUTDATE,
                'message' => ResponseCode::$codeMap[ResponseCode::VOTE_WAS_OUTDATE],
            ]);
        }

        $vote_question->vote_total++;
        $vote_question->save();

        $request_options_id = json_decode($request->vote_options_id, true);
        foreach ($request_options_id as $option_id) {
            $option = VoteOption::find($option_id);
            //检查投票选项id是否存在
            if (!$option) {
                return response()->json([
                    'code' => ResponseCode::VOTE_OPTION_NOT_FOUND,
                    'message' => ResponseCode::$codeMap[ResponseCode::VOTE_OPTION_NOT_FOUND],
                ]);
            }
            $option->vote_total++;
            $option->save();
        }

        $vote_user = new VoteUser;
        $vote_user->user_id = $user->id;
        $vote_user->vote_question_id = $request->vote_question_id;
        $vote_user->options_id = $request->vote_options_id;
        $vote_user->save();

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户参加了投票',
                'vote_question_id' => $request->vote_options_id,
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '投票成功！',
                'data' => [
                    'vote_question_id' => $request->vote_question_id,
                    'vote_options_id' => $request->vote_options_id,
                ]
            ],
        );
    }
}
