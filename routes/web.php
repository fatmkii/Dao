<?php

use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



// 测试用
// Route::get('/MAC_test', [TestController::class, 'MAC_test']);
// Route::middleware('auth:sanctum')->get('/test', [TestController::class, 'test']);
// Route::get('/test', [TestController::class, 'test'])->block();
// Route::get('/test/login', [TestController::class, 'login']);
// Route::middleware('auth')->get('/test/get_user', [TestController::class, 'get_user']);
// Route::get('/test/request', [TestController::class, 'request']);
// Route::get('/test/reply_queue', [TestController::class, 'reply_queue']);
// Route::get('/test/index', [TestController::class, 'index']);

// Route::prefix('vue')->group(function () {
//     Route::get('test', [TestController::class, 'vue_test']);
//     Route::get('test/{id}', [TestController::class, 'vue_test']);
// });
    
// Route::prefix('bs')->group(function () {
//     Route::get('test', [TestController::class, 'bs_test']);
//     Route::get('test/{id}', [TestController::class, 'bs_test']);
// });

Route::get('/{path}', function () {
    return view('index');
})->where('path','.*');
