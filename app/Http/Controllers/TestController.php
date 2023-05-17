<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Thread;
use App\Models\Forum;
use Laravel\Sanctum\HasApiTokens;
use App\Jobs\PostProcess;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $request->session()->push('user.teams', 'developers');
        return  App::getLocale();
    }

    public function get_user()
    {
        return response()->json([
            'code' => 200,
            'message' => '登录成功',
            'data' => [
                'binggan' => Auth::user(),
            ],
        ]);
    }


    public function login()
    {
        Auth::loginUsingId(100001,true);
        return response()->json([
            'code' => 200,
            'message' => '登录成功',
            'data' => [
                'binggan' => Auth::user(),
            ],
        ]);
    }

    public function request(Request $request)
    {
        // $all=$request->cookie();

        return response()->json(['name', 'chin']);
    }

    public function reply_queue()
    {
        $reply = new Post([
            'forum_id' => '1',
            'thread_id' => '100001',
            'floor' => '5',
            'random_head' => '2',
            'content' => '内容',
        ]);
        // $reply->save();

        PostProcess::dispatch(
            new Post([
                'forum_id' => '1',
                'thread_id' => '100001',
                'floor' => '5',
                'random_head' => '2',
                'content' => '内容',
            ])
        );
        Redis::set('akey', 'avalue');

        return Post::all();
    }

    public function index()
    {
        return view('index');
    }

    public function vue_test($id = '')
    {
        return view('vue_test' . $id);
    }

    public function bs_test($id = '')
    {
        return view('bs_test' . $id);
    }
}
