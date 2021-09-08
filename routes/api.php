<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ForumController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ThreadController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\CommonController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\VoteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



//Auth系列
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin_login', [AuthController::class, 'admin_login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//Forum系列
Route::prefix('forums')->group(function () {
    Route::get('', [ForumController::class, 'index']); //查看板块列表
    Route::get('/{forum_id}', [ForumController::class, 'show'])->middleware('CheckBinggan:show'); //查看板块内主题列表
});
// Route::apiResource('forums', ForumController::class);

//thread系列
Route::prefix('threads')->group(function () {
    Route::get('/{Thread_id}', [ThreadController::class, 'show'])->middleware('CheckBinggan:show'); //查看主题
    Route::post('/create', [ThreadController::class, 'create'])->middleware('CheckBinggan:create'); //发新主题
});
// Route::apiResource('threads', ThreadController::class)->middleware('auth:sanctum');

//Post系列
Route::prefix('posts')->group(function () {
    Route::post('/create', [PostController::class, 'create'])->middleware('CheckBinggan:create'); //新帖子
    Route::delete('/{id}', [PostController::class, 'destroy'])->middleware('CheckBinggan:create'); //删除帖子
    Route::post('/create_roll', [PostController::class, 'create_roll'])->middleware('CheckBinggan:create'); //新roll点
    Route::put('/recover/{post_id}', [PostController::class, 'recover'])->middleware('CheckBinggan:create'); //恢复删除的帖子
});
// Route::apiResource('posts', PostController::class);

//Vote系列
Route::prefix('votes')->group(function () {
    Route::get('/{vote_id}', [VoteController::class, 'show']); //显示投票结果
    Route::post('', [VoteController::class, 'store'])->middleware('CheckBinggan:create');  //用户参与投票
});

//User系列
Route::prefix('user')->group(function () {
    Route::post('/show', [UserController::class, 'show'])->middleware('auth:sanctum'); //获得用户信息
    Route::post('/register', [UserController::class, 'create']);   //新建饼干
    Route::post('/reward', [UserController::class, 'reward'])->middleware('CheckBinggan:create');     //打赏
    Route::get('/check_reg_record', [UserController::class, 'check_reg_record']); //返回注册记录TTL
    Route::post('/pingbici_set', [UserController::class, 'pingbici_set']);     //设定屏蔽词
    Route::post('/my_emoji_set', [UserController::class, 'my_emoji_set']);     //设定表情包
});

//Admin系列
Route::middleware('CheckTokenCan:admin', 'auth:sanctum')->prefix('admin')->group(function () {
    Route::post('/thread_delete', [AdminController::class, 'thread_delete']); //删主题
    Route::delete('/post_delete/{post_id}', [AdminController::class, 'post_delete']); //删帖
    Route::put('/post_recover/{post_id}', [AdminController::class, 'post_recover']); //恢复帖子
    Route::post('/post_delete_all', [AdminController::class, 'post_delete_all']); //删主题内该作者全部回帖
    Route::post('/user_ban', [AdminController::class, 'user_ban']); //碎饼
    Route::post('/user_lock', [AdminController::class, 'user_lock']); //封id（临时）
    Route::post('/thread_set_top', [AdminController::class, 'thread_set_top']); //设置置顶
    Route::post('/thread_cancel_top', [AdminController::class, 'thread_cancel_top']); //取消置顶
    // Route::post('/check_jingfen', [AdminController::class, 'check_jingfen']); //查精分
});

//SuperAdmin系列
Route::middleware('CheckTokenCan:super_admin', 'auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/check_user_post', [AdminController::class, 'check_user_post']); //查询用户发帖记录
});


//杂项
Route::get('/emoji', [CommonController::class, 'emoji_index']);
Route::get('/subtitles', [CommonController::class, 'subtitles_index']);
Route::get('/random_heads', [CommonController::class, 'random_heads_index']);
