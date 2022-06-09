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
use App\Http\Controllers\API\BattleController;
use App\Http\Controllers\API\VoteController;
use App\Http\Controllers\API\GambleController;
use App\Http\Controllers\API\CrowdController;

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
    Route::get('/delay/{forum_id}', [ForumController::class, 'show_delay'])->middleware('CheckBinggan:show'); //查看板块内延时发送主题列表
});
// Route::apiResource('forums', ForumController::class);

//thread系列
Route::prefix('threads')->group(function () {
    Route::get('/{Thread_id}', [ThreadController::class, 'show'])->middleware('CheckBinggan:show'); //查看主题
    Route::post('/create', [ThreadController::class, 'create'])->middleware('CheckBinggan:create'); //发新主题
    Route::delete('/delay/{Thread_id}', [ThreadController::class, 'delay_thread_withdraw'])->middleware('CheckBinggan:create'); //撤回延时主题
    Route::post('/change_color', [ThreadController::class, 'change_color'])->middleware('CheckBinggan:create'); //改标题颜色
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

//Gamble系列
Route::prefix('gambles')->group(function () {
    Route::get('/{gamble_id}', [GambleController::class, 'show']); //显示菠菜结果
    Route::post('', [GambleController::class, 'store'])->middleware('CheckBinggan:create');  //用户投注
    Route::post('/close', [GambleController::class, 'close'])->middleware('auth:sanctum');  //开奖菠菜（只能由管理员操作）
    Route::post('/repeal', [GambleController::class, 'repeal'])->middleware('auth:sanctum');  //中止菠菜（只能由管理员操作）
});

//Battle系列
Route::prefix('battles')->group(function () {
    Route::get('/{battle_id}', [BattleController::class, 'show']); //显示大乱斗结果
    Route::post('', [BattleController::class, 'create'])->middleware('CheckBinggan:create');  //用户发起大乱斗
    Route::post('/c_roll', [BattleController::class, 'challenger_roll'])->middleware('CheckBinggan:create');  //挑战者投色子
    Route::post('/i_roll', [BattleController::class, 'initiator_roll'])->middleware('CheckBinggan:create');  //发起者投色子
});

//Crowd系列
Route::prefix('crowds')->group(function () {
    Route::get('/{crowd_id}', [CrowdController::class, 'show']); //显示众筹结果
    Route::post('', [CrowdController::class, 'create'])->middleware('CheckBinggan:create');  //用户参加众筹
    Route::post('/repeal', [CrowdController::class, 'repeal'])->middleware('auth:sanctum');  //中止众筹（只能由管理员操作）
});

//User系列
Route::prefix('user')->group(function () {
    Route::post('/show', [UserController::class, 'show'])->middleware('auth:sanctum'); //获得用户信息
    Route::post('/register', [UserController::class, 'create']);   //新建饼干
    Route::post('/reward', [UserController::class, 'reward'])->middleware('CheckBinggan:create');     //打赏
    Route::get('/check_reg_record', [UserController::class, 'check_reg_record']); //返回注册记录TTL
    Route::post('/pingbici_set', [UserController::class, 'pingbici_set'])->middleware('CheckBinggan:create');     //设定屏蔽词
    Route::post('/my_emoji_set', [UserController::class, 'my_emoji_set'])->middleware('CheckBinggan:create');     //设定表情包
    Route::post('/my_emoji_add', [UserController::class, 'my_emoji_add'])->middleware('CheckBinggan:create');     //追加表情包
    Route::post('/water_unlock', [UserController::class, 'water_unlock'])->middleware('CheckBinggan:create');     //解除灌水锁定
    Route::post('/user_lv_up', [UserController::class, 'user_lv_up'])->middleware('CheckBinggan:create');
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

//IncomeStatement系列
Route::prefix('income')->group(function () {
    Route::get('/show', [UserController::class, 'income_show'])->middleware('CheckBinggan:show'); //查看olo收益表
});

//杂项
Route::get('/emoji', [CommonController::class, 'emoji_index']);
Route::get('/subtitles', [CommonController::class, 'subtitles_index']);
Route::get('/random_heads', [CommonController::class, 'random_heads_index']);
Route::get('/captcha', [CommonController::class, 'get_captcha']);
Route::post('/img_upload', [CommonController::class, 'img_upload'])->middleware('CheckBinggan:create');//上传图片