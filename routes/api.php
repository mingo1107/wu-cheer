<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CleanerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EarthDataController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLogController;
use App\Http\Controllers\VerifierController;
use App\Http\Controllers\VerifierPlatform\VerifierAccountController;
use App\Http\Controllers\VerifierPlatform\VerifierVerifyController;
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

// 核銷平台路由
Route::prefix('verifier-platform')->group(function () {
    // 公開路由 - 不需要認證
    Route::post('account/login', [VerifierAccountController::class, 'login'])->name('api.verifier-platform.account.login');

    // 需要認證的路由
    Route::middleware('auth:verifier')->group(function () {
        // 核銷人員登出
        Route::post('account/logout', [VerifierAccountController::class, 'logout'])->name('api.verifier-platform.account.logout');
        // 取得目前核銷人員資訊
        Route::get('account/me', [VerifierAccountController::class, 'me'])->name('api.verifier-platform.account.me');

        // 核銷作業
        Route::post('verify/pre-check', [VerifierVerifyController::class, 'preCheck'])->name('api.verifier-platform.verify.pre-check');
        Route::post('verify', [VerifierVerifyController::class, 'verify'])->name('api.verifier-platform.verify');
        Route::post('verify/batch', [VerifierVerifyController::class, 'batchVerify'])->name('api.verifier-platform.verify.batch');
        Route::get('verify/stats', [VerifierVerifyController::class, 'stats'])->name('api.verifier-platform.verify.stats');
    });
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

    //@ dashboard
    // 儀表板統計資料
    Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');

    //@ user
    // 使用者管理 CRUD (需要管理員權限)
    Route::middleware('admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    //@ verifier
    // 核銷人員管理 CRUD (需要管理員權限)
    Route::middleware('admin')->group(function () {
        Route::apiResource('verifiers', VerifierController::class);
        // 核銷人員額外功能
        Route::get('verifiers/active/list', [VerifierController::class, 'active'])->name('api.verifiers.active');
        Route::get('verifiers/stats/overview', [VerifierController::class, 'stats'])->name('api.verifiers.stats');
    });

    //@ user-log
    // 使用者操作記錄 (需要管理員權限)
    Route::middleware('admin')->group(function () {
        Route::get('user-logs', [UserLogController::class, 'index'])->name('api.user-logs.index');
    });

    //@ customer
    // 客戶管理 CRUD
    Route::apiResource('customers', CustomerController::class);
    // 客戶額外功能
    Route::get('customers/active/list', [CustomerController::class, 'active'])->name('api.customers.active');
    Route::get('customers/search', [CustomerController::class, 'search'])->name('api.customers.search');
    Route::get('customers/stats/overview', [CustomerController::class, 'stats'])->name('api.customers.stats');

    //@ cleaner 清運業者 CRUD
    Route::apiResource('cleaners', CleanerController::class);
    // 清運業者車輛管理
    Route::get('cleaners/{cleanerId}/vehicles', [CleanerController::class, 'getVehicles'])->name('api.cleaners.vehicles');
    Route::post('cleaners/{cleanerId}/vehicles', [CleanerController::class, 'storeVehicle'])->name('api.cleaners.vehicles.store');
    Route::put('cleaners/{cleanerId}/vehicles/{vehicleId}', [CleanerController::class, 'updateVehicle'])->name('api.cleaners.vehicles.update');
    Route::delete('cleaners/{cleanerId}/vehicles/{vehicleId}', [CleanerController::class, 'destroyVehicle'])->name('api.cleaners.vehicles.destroy');

    //@ earth-data 土單資料 CRUD
    Route::apiResource('earth-data', EarthDataController::class);
    // 調整土單明細（增加/減少張數）
    Route::post('earth-data/{id}/details/adjust', [EarthDataController::class, 'adjustDetails'])->name('api.earth-data.adjust-details');
    // 結案工程
    Route::post('earth-data/{id}/close', [EarthDataController::class, 'close'])->name('api.earth-data.close');
    // 取得指定工程的使用明細
    Route::get('earth-data/{id}/details', [\App\Http\Controllers\EarthDataUsageController::class, 'details'])->name('api.earth-data.details');
    // 匯出指定工程的使用明細（xlsx）
    Route::get('earth-data/{id}/details/export', [\App\Http\Controllers\EarthDataUsageController::class, 'detailsExport'])->name('api.earth-data.details-export');
    // 更新明細狀態
    Route::put('earth-data/{id}/details/{detailId}/status', [\App\Http\Controllers\EarthDataUsageController::class, 'updateDetailStatus'])->name('api.earth-data.details.update-status');
    // 批量回收明細
    Route::post('earth-data/{id}/details/recycle', [\App\Http\Controllers\EarthDataUsageController::class, 'recycleDetails'])->name('api.earth-data.details.recycle');
    // 批量更新明細狀態
    Route::post('earth-data/{id}/details/batch-update-status', [\App\Http\Controllers\EarthDataUsageController::class, 'batchUpdateStatus'])->name('api.earth-data.details.batch-update-status');
    // 批量更新明細的使用起訖日期
    Route::post('earth-data/{id}/details/batch-update-dates', [\App\Http\Controllers\EarthDataUsageController::class, 'batchUpdateDates'])->name('api.earth-data.details.batch-update-dates');
    // 取得使用統計
    Route::get('earth-data/{id}/usage/stats', [\App\Http\Controllers\EarthStatisticsController::class, 'stats'])->name('api.earth-data.usage-stats');

    //@ announcement 公告欄 CRUD
    Route::apiResource('announcements', AnnouncementController::class);

    //@ common lists
    Route::get('common/cleaners', [CommonController::class, 'getCleanerList'])->name('api.common.cleaners');
    Route::get('common/customers', [CommonController::class, 'getCustomerList'])->name('api.common.customers');
    Route::get('common/earth-data/datalist', [CommonController::class, 'getEarthDataDatalist'])->name('api.common.earth-data.datalist');
    Route::get('common/earth-data-detail/status-list', [CommonController::class, 'getEarthDataDetailStatusList'])->name('api.common.earth-data-detail.status-list');

    // 通用 API
    Route::prefix('common')->name('api.common.')->group(function () {
        Route::get('/customers', [\App\Http\Controllers\CommonController::class, 'customers'])->name('customers');
        Route::get('/cleaners', [\App\Http\Controllers\CommonController::class, 'cleaners'])->name('cleaners');
        Route::get('/soil-types', [\App\Http\Controllers\CommonController::class, 'soilTypes'])->name('soil-types');
        Route::get('/meter-types', [\App\Http\Controllers\CommonController::class, 'meterTypes'])->name('meter-types');
    });
});
