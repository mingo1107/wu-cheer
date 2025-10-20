<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EarthDataController;

Route::get('/', function () {
    return view('welcome');
});

// 登入頁面路由
Route::get('/login', function () {
    return view('cms-app');
})->name('login');

// 列印路由（需放在 SPA catch-all 前）
Route::get('/print/earth-data/{id}/pending', [EarthDataController::class, 'printPending'])->name('print.earth-data.pending');

// SPA 路由 - 所有前端路由都指向同一個視圖
Route::get('/{any}', function () {
    return view('cms-app');
})->where('any', '.*');
