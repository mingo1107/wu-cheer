<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// 登入頁面路由
Route::get('/login', function () {
    return view('cms-app');
})->name('login');

// SPA 路由 - 所有前端路由都指向同一個視圖
Route::get('/{any}', function () {
    return view('cms-app');
})->where('any', '.*');
