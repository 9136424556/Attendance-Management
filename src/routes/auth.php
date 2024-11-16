<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    //会員登録ページ
    Route::get('/register', [RegisteredUserController::class, 'create'])
                ->name('register');
    //会員登録処理
    Route::post('/register', [RegisteredUserController::class, 'store']);

    //ログインページ
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
                ->name('login'); 
    //ログイン処理
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

});

Route::middleware('auth')->group(function () {
     //ログアウト処理（ボタンを押して実行）
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
             ->name('logout');
});