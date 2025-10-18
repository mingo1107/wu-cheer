<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CleanerController;
use App\Http\Controllers\EarthDataController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

    //@ account
    // 帳戶登出
    Route::post('account/logout', [AccountController::class, 'logout'])->name('api.account.logout');
    // 取得目前帳戶資訊
    Route::get('account/me', [AccountController::class, 'me'])->name('api.account.me');
    // 修改密碼
    Route::post('account/change-password', [AccountController::class, 'changePassword'])->name('api.account.change-password');

    //@ user
    // 使用者管理 CRUD
    Route::apiResource('users', UserController::class);

    //@ customer
    // 客戶管理 CRUD
    Route::apiResource('customers', CustomerController::class);
    // 客戶額外功能
    Route::get('customers/active/list', [CustomerController::class, 'active'])->name('api.customers.active');
    Route::get('customers/search', [CustomerController::class, 'search'])->name('api.customers.search');
    Route::get('customers/stats/overview', [CustomerController::class, 'stats'])->name('api.customers.stats');

    //@ cleaner 清運業者 CRUD
    Route::apiResource('cleaners', CleanerController::class);

    //@ earth-data 土單資料 Excel 介面
    Route::get('earth-data', [EarthDataController::class, 'index'])->name('api.earth-data.index');
    Route::get('earth-data/{id}', [EarthDataController::class, 'show'])->whereNumber('id')->name('api.earth-data.show');
    Route::post('earth-data/bulk-upsert', [EarthDataController::class, 'bulkUpsert'])->name('api.earth-data.bulk-upsert');
    Route::post('earth-data/bulk-delete', [EarthDataController::class, 'bulkDelete'])->name('api.earth-data.bulk-delete');

    //@ announcement 公告欄 CRUD
    Route::apiResource('announcements', AnnouncementController::class);
});
