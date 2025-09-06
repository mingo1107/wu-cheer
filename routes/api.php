<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
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
    // 帳戶登入
    Route::post('account/login', [AccountController::class, 'login'])->name('api.account.login');
});

// 需要認證的路由
Route::middleware('auth:api')->group(function () {
    // 帳戶登出
    Route::post('account/logout', [AccountController::class, 'logout'])->name('api.account.logout');

    // 取得目前帳戶資訊
    Route::get('account/me', [AccountController::class, 'me'])->name('api.account.me');

    // 使用者管理 CRUD
    Route::apiResource('users', UserController::class);
});
