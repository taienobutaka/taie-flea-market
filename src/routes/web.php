<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;

// ホームページ
Route::get('/', function () {
    return view('items');
});

// ユーザー登録関連
Route::prefix('register')->group(function () {
    Route::get('/', [RegisterController::class, 'showRegisterForm'])->name('register.form'); // 登録フォーム表示
    Route::post('/', [RegisterController::class, 'register'])->name('register'); // 登録処理
});

// ログイン関連
Route::get('/login', [RegisterController::class, 'showLoginForm'])->name('login'); // ログインフォーム表示
Route::post('/login', [RegisterController::class, 'login'])->name('login.post'); // ログイン処理

// メール認証関連
Route::prefix('email')->middleware('auth')->group(function () {
    Route::get('/verify', function () {
        return view('auth.verify-email'); // 認証メール送付画面
    })->name('verification.notice');

    Route::get('/verify/{id}/{hash}', [RegisterController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('/resend', [RegisterController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
});

// 認証メール再送信
Route::post('/email/resend', [RegisterController::class, 'resendVerificationEmail'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.resend');

Route::get('/verification', [RegisterController::class, 'showVerificationForm'])->name('verification');

// プロフィール設定関連
Route::get('/create-profile', [UserController::class, 'showCreateProfile'])->name('profile.create'); // プロフィール設定画面
Route::post('/create-profile', [UserController::class, 'storeProfile'])->name('profile.store');

// プロフィール画像アップロード
Route::post('/upload-image', [UserController::class, 'uploadImage'])->name('uploadImage');
