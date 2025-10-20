<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CommonController;
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

    //@ earth-data 土單資料 CRUD
    Route::apiResource('earth-data', EarthDataController::class);
    // 調整土單明細（增加/減少張數）
    Route::post('earth-data/{id}/details/adjust', [EarthDataController::class, 'adjustDetails'])->name('api.earth-data.adjust-details');
    // 取得指定工程的使用明細
    Route::get('earth-data/{id}/details', [EarthDataController::class, 'details'])->name('api.earth-data.details');
    // 匯出指定工程的使用明細（CSV）
    Route::get('earth-data/{id}/details/export', [EarthDataController::class, 'detailsExport'])->name('api.earth-data.details-export');

    //@ announcement 公告欄 CRUD
    Route::apiResource('announcements', AnnouncementController::class);

    //@ common lists
    Route::get('common/cleaners', [CommonController::class, 'getCleanerList'])->name('api.common.cleaners');
    Route::get('common/customers', [CommonController::class, 'getCustomerList'])->name('api.common.customers');
    Route::get('common/earth-data/datalist', [CommonController::class, 'getEarthDataDatalist'])->name('api.common.earth-data.datalist');
});
