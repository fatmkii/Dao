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
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
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
            'vote_question_id' => 'integer|required',
            'vote_options' => 'json|required|max:500',
        ]);

        $user = $request->user;

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

        //检查用户是否已经投过票
        if (DB::table('vote_users_' . intval($request->vote_question_id / 10000))
            ->where('vote_question_id', $request->vote_question_id)
            ->where('user_id', $user->id)->exists()
        ) {
            return response()->json([
                'code' => ResponseCode::VOTE_HAVE_BEEN_VOTE,
                'message' => ResponseCode::$codeMap[ResponseCode::VOTE_HAVE_BEEN_VOTE],
            ]);
        };

        $vote_question->vote_total++;
        $vote_question->save();

        $request_options_id = json_decode($request->vote_options, true);
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
        $vote_user->setSuffix(intval($request->vote_question_id / 10000));
        $vote_user->user_id = $user->id;
        $vote_user->vote_question_id = $request->vote_question_id;
        $vote_user->options_id = $request->vote_options;
        $vote_user->save();

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户参加了投票',
                'vote_question_id' => $request->vote_options,
            ]
        );

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '投票成功！',
                'data' => [
                    'vote_question_id' => $request->vote_question_id,
                    'vote_options_id' => $request->vote_options,
                ]
            ],
        );
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

        if ($user) {
            $user_choices = $vote_question->VoteUserChoices($user->id);
        } else {
            $user_choices = "";
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
}
