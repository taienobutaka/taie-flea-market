<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;

// ホームページ（商品一覧）
Route::get('/', [ItemController::class, 'index'])->name('items.index');

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

// 商品出品関連（認証必須）
Route::middleware(['auth'])->group(function () {
    Route::get('/sell', [ItemController::class, 'create'])->name('sell.form'); // 商品出品画面
    Route::post('/items/upload-image', [ItemController::class, 'uploadImage'])->name('item.uploadImage'); // 商品画像アップロード
    Route::post('/items/remove-image', [ItemController::class, 'removeImage'])->name('item.removeImage'); // 商品画像削除
    Route::post('/items', [ItemController::class, 'store'])->name('item.store'); // 商品出品処理

    // 商品購入関連
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase');
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'showAddressForm'])->name('purchase.address');
    Route::put('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');
    Route::post('/purchase/confirm/{item_id}', [PurchaseController::class, 'confirm'])->name('purchase.confirm');
});

// マイページ関連（認証必須）
Route::get('/mypage', [UserController::class, 'showMypage'])->name('mypage')->middleware('auth');

// プロフィール設定関連（認証必須）
Route::middleware(['auth'])->group(function () {
    Route::get('/mypage/profile', [UserController::class, 'showEditProfile'])->name('profile.edit'); // プロフィール編集画面
    Route::post('/mypage/profile', [UserController::class, 'updateProfile'])->name('profile.update'); // プロフィール更新処理
    Route::post('/mypage/profile/image', [UserController::class, 'uploadImage'])->name('profile.uploadImage'); // プロフィール画像アップロード
});

// ログアウト
Route::post('/logout', [RegisterController::class, 'logout'])->name('logout');

// 商品関連
Route::get('/items', [ItemController::class, 'index'])->name('items.index'); // 商品一覧画面
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.show'); // 商品詳細画面
Route::post('/item/{item_id}/comment', [ItemController::class, 'addComment'])->name('item.comment'); // 商品コメント投稿

// 購入関連のルート
Route::prefix('purchase')->group(function () {
    Route::get('/{item_id}', [PurchaseController::class, 'show'])->name('purchase');
    Route::post('/{item_id}/confirm', [PurchaseController::class, 'confirm'])->name('purchase.confirm');
    Route::get('/{item_id}/complete', [PurchaseController::class, 'complete'])->name('purchase.complete');
});
