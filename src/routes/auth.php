<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
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
    // メール送信リンクの表示
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
    ->name('verification.notice');
    // メール認証確認
    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed','throttle:6,1'])
    ->name('verification.verify');
    // メール再送信
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->name('verification.send');

     //ログアウト処理（ボタンを押して実行）
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
             ->name('logout');
});