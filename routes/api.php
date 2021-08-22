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
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//Forum系列
Route::apiResource('forums', ForumController::class);

//thread系列
Route::post('/threads/create', [ThreadController::class, 'create'])->name('thread.create');
Route::apiResource('threads', ThreadController::class);

//Post系列
Route::prefix('posts')->group(function () {
    Route::post('/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/create_roll', [PostController::class, 'create_roll']);
    Route::put('/recover/{post_id}', [PostController::class, 'recover']);
});
Route::apiResource('posts', PostController::class);

//User系列
Route::prefix('user')->group(function () {
    Route::post('/show', [UserController::class, 'show'])->middleware('auth:sanctum'); //获得用户信息
    Route::post('/register', [UserController::class, 'create']);   //新建饼干
    Route::post('/reward', [UserController::class, 'reward']);     //打赏
    Route::get('/check_reg_record', [UserController::class, 'check_reg_record']); //返回注册记录TTL
    Route::post('/pingbici_set', [UserController::class, 'pingbici_set']);     //设定屏蔽词
    Route::post('/my_emoji_set', [UserController::class, 'my_emoji_set']);     //设定表情包
});

//Admin系列（记得CheckTokenCan要在auth:sanctum的前面）
Route::middleware('CheckTokenCan:admin', 'auth:sanctum')->prefix('admin')->group(function () {
    Route::post('/thread_delete', [AdminController::class, 'thread_delete']); //删主题
    Route::delete('/post_delete/{post_id}', [AdminController::class, 'post_delete']); //删帖
    Route::put('/post_recover/{post_id}', [AdminController::class, 'post_recover']); //恢复帖子
    Route::post('/post_delete_all', [AdminController::class, 'post_delete_all']); //删主题内该作者全部回帖
    Route::post('/user_ban', [AdminController::class, 'user_ban']); //碎饼
    Route::post('/user_lock', [AdminController::class, 'user_lock']); //封id（临时）
    Route::post('/thread_set_top', [AdminController::class, 'thread_set_top']); //设置置顶
    Route::post('/thread_cancel_top', [AdminController::class, 'thread_cancel_top']); //取消置顶
});


//杂项
Route::get('/emoji', [CommonController::class, 'emoji_index']);
Route::get('/subtitles', [CommonController::class, 'subtitles_index']);
Route::get('/random_heads', [CommonController::class, 'random_heads_index']);
