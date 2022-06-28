<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use App\Common\ResponseCode;
use App\Jobs\ProcessUserActive;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

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
            return Forum::where('status', '<>', 0) //把status为0的隐藏掉
                ->orderBy('sub_id', 'asc') //把个人岛放到后面
                ->get();
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
        $request->validate([
            'binggan' => 'string|nullable',
            'page' => 'integer|nullable',
            'threads_per_page' => 'integer|nullable|max:100|min:1',
            'subtitles_excluded' => 'json|nullable', //要排除的副标题
            'search_title' => 'string|max:100', //搜索标题
        ]);

        //用redis记录，全局每10秒搜索20次限制
        if ($request->has('search_title')) {
            if (Redis::exists('search_record_global')) {
                Redis::incr('search_record_global');
                if (Redis::GET('search_record_global') > 20) {
                    return response()->json([
                        'code' => ResponseCode::SEARCH_TOO_MANY,
                        'message' => ResponseCode::$codeMap[ResponseCode::SEARCH_TOO_MANY],
                    ]);
                }
            } else {
                Redis::setex('search_record_global',  10, 1);
            }
        }

        $CurrentForum = Forum::find($forum_id);
        $user = $request->user;
        $threads_per_page = $request->threads_per_page ? $request->threads_per_page : 50; //默认值是50个主题每页
        $subtitles_excluded = json_decode($request->subtitles_excluded, true);

        //隐藏不可见岛
        if ($CurrentForum->status == 0) {
            return response()->json([
                'code' => ResponseCode::SUCCESS,
                'message' => '这里什么都没有',
                'forum_data' => $CurrentForum->makeVisible('banners'),
                'threads_data' => "",
                'subtitles_exclude' => "",
            ]);
        }

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

        $threads = $CurrentForum->threads()->where('is_deleted', 0)->where('is_delay', 0);

        //搜索标题
        if ($request->has('search_title')) {
            $threads->where('title', 'like', '%' . $request->query('search_title') . '%');
        }

        //搜索副标题
        if ($subtitles_excluded != [] || $subtitles_excluded != null) {
            $threads->whereNotIn('sub_title', $subtitles_excluded);
        }

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
            case 2: //按照24小时日清模式(目前咒版不清标题)
                break;
        }


        //加入公告以及排序
        $threads
            ->orWhere(function ($query) {  //加入全岛公告（sub_id=99）
                $query->where('is_deleted', 0)
                    ->where('sub_id', 99);
            })
            ->orderBy('sub_id', 'desc')->orderBy('updated_at', 'desc'); //sub_id是用来把公告等提前的



        //记录搜索行为
        if ($request->has('search_title')) {
            ProcessUserActive::dispatch(
                [
                    'binggan' => $user->binggan,
                    'user_id' => $user->id,
                    'active' => '用户进行了搜索',
                    'content' => '关键词：' . $request->query('search_title'),
                    'forum_id' => $forum_id,
                ]
            );
        }

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => ResponseCode::$codeMap[ResponseCode::SUCCESS],
            'forum_data' => $CurrentForum->makeVisible('banners'),
            'threads_data' => $threads->paginate($threads_per_page),
            'subtitles_exclude' => $subtitles_excluded,
        ]);
    }

    public function show_delay(Request $request, $forum_id)
    {
        $request->validate([
            'binggan' => 'string|nullable',
            'page' => 'integer|nullable',
        ]);



        $CurrentForum = Forum::find($forum_id);
        $user = $request->user;

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

        $threads = $CurrentForum->threads()->where('is_deleted', 0)->where('is_delay', 1)->paginate(30);

        //如果有提供binggan，为每个thread输入binggan，用来判断is_your_thread（为前端提供是否是用户自己帖子的判据）
        if ($request->query('binggan')) {
            foreach ($threads as $thread) {
                $thread->setBinggan($request->query('binggan'));
                $thread->makeVisible('is_your_thread');
            }
        }

        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'message' => ResponseCode::$codeMap[ResponseCode::SUCCESS],
            'forum_data' => $CurrentForum->makeVisible('banners'),
            'threads_data' => $threads,
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
