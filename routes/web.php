<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EarthDataController;

// 列印路由（需放在 SPA catch-all 前）
Route::get('/print/earth-data/{id}/pending', [EarthDataController::class, 'printPending'])->name('print.earth-data.pending');
Route::get('/print/earth-data/{id}/selected', [EarthDataController::class, 'printSelected'])->name('print.earth-data.selected');

// SPA 路由 - 所有前端路由都指向同一個視圖
// 包括根路由 /，這樣 Vue Router 才能正確處理所有路由
Route::get('/{any}', function () {
    return view('cms-app');
})->where('any', '.*');
