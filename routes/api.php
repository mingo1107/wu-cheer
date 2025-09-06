<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 公開路由 - 不需要認證
Route::group([], function () {
    // 使用者登入
    Route::post('user/login', [UserController::class, 'login'])->name('api.user.na.login');
});

// 需要認證的路由
Route::middleware('auth')->group(function () {
    // 使用者登出
    Route::post('user/logout', [UserController::class, 'logout'])->name('api.user.na.logout');
    
    // 取得目前使用者資訊
    Route::get('user/me', [UserController::class, 'me'])->name('api.user.na.me');
});
