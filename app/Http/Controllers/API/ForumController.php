<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use App\Common\ResponseCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ForumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $forums = Cache::remember('forums_cache',  24 * 3600, function () {
            return Forum::where('id', '<>', 0)->orderBy('sub_id', 'asc')->get(); //把0岛隐藏掉
        });
        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'data' => $forums,
        ]);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $forum_id)
    {
        $CurrentForum = Forum::find($forum_id);
        $user = $request->user;
        // $user = User::where('binggan', $request->query('binggan'))->first();
        //判断是否可无饼干访问的板块
        if (!$CurrentForum->is_anonymous && !$user) {
            return response()->json([
                'code' => ResponseCode::USER_NOT_FOUND,
                'message' => '本小岛需要饼干才能查看喔',
            ]);
        }

        //判断是否达到可以访问板块的最少奥利奥
        if ($CurrentForum->accessible_coin > 0) {
            if (!$user) {
                return response()->json([
                    'code' => ResponseCode::USER_NOT_FOUND,
                    'message' => '本小岛需要饼干才能查看喔',
                ]);
            }
            if ($user->coin < $CurrentForum->accessible_coin && $user->admin == 0) {
                return response()->json([
                    'code' => ResponseCode::THREAD_UNAUTHORIZED,
                    'message' => sprintf("本小岛需要拥有大于%u奥利奥才能查看喔", $CurrentForum->accessible_coin),
                ]);
            }
        }

        $threads = $CurrentForum->threads()->where('is_deleted', 0);

        //各种日清模式
        switch ($CurrentForum->is_nissin) {
            case 0:
                break;
            case 1: //按照8点日清模式
                $hour_now = Carbon::now()->hour;
                if ($hour_now >= 8) { //根据时间确定8点日清的节点
                    $nissin_breakpoint = Carbon::today()->addHours(8);
                } else {
                    $nissin_breakpoint = Carbon::yesterday()->addHours(8);
                }
                $threads->where('created_at', '>', $nissin_breakpoint)
                    ->orWhere(function ($query) use ($forum_id) {  //但要把本版公告加回来(sub_id=10)
                        $query->where('forum_id', $forum_id)
                            ->where('sub_id', 10)
                            ->where('is_deleted', 0);
                    });
                break;
            case 2: //按照24小时日清模式
                break;
        }

        $threads
            ->orWhere(function ($query) {  //加入全岛公告（sub_id=99）
                $query->where('is_deleted', 0)
                    ->where('sub_id', 99);
            })
            ->orderBy('sub_id', 'desc')->orderBy('updated_at', 'desc'); //sub_id是用来把公告等提前的

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => ResponseCode::$codeMap[ResponseCode::SUCCESS],
            'forum_data' => $CurrentForum->makeVisible('banners'),
            'threads_data' => $threads->paginate(30),
        ]);
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
