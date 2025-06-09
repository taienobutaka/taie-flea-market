<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| 未ログインユーザーもアクセス可能なルート
|--------------------------------------------------------------------------
| これらのルートは認証なしでアクセス可能です。
| 商品一覧画面と商品詳細画面のみが公開されています。
*/
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/search', [ItemController::class, 'search'])->name('items.search');
Route::get('/item/{id}', [ItemController::class, 'show'])->name('item.show');

/*
|--------------------------------------------------------------------------
| ユーザー登録関連のルート
|--------------------------------------------------------------------------
| 新規ユーザー登録のためのルートです。
| 認証は不要です。
*/
Route::prefix('register')->group(function () {
    Route::get('/', [RegisterController::class, 'showRegisterForm'])->name('register.form');
    Route::post('/', [RegisterController::class, 'register'])->name('register');
});

/*
|--------------------------------------------------------------------------
| ログイン関連のルート
|--------------------------------------------------------------------------
| ユーザーのログイン・ログアウト処理のためのルートです。
| 認証は不要です。
*/
Route::get('/login', [RegisterController::class, 'showLoginForm'])->name('login');
Route::post('/login', [RegisterController::class, 'login']);
Route::post('/logout', [RegisterController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| メール認証関連のルート
|--------------------------------------------------------------------------
| メールアドレスの認証に関するルートです。
| authミドルウェアで保護されており、ログイン済みユーザーのみアクセス可能です。
*/
Route::prefix('verification')->middleware(['auth'])->group(function () {
    Route::get('/', function () {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('profile.create');
        }
        return view('verification');
    })->name('verification.notice');

    Route::get('/{id}/{hash}', [RegisterController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('/resend', [RegisterController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| プロフィール設定関連のルート
|--------------------------------------------------------------------------
| ユーザープロフィールの設定に関するルートです。
| auth, verifiedミドルウェアで保護されており、
| ログイン済みでメール認証が完了しているユーザーのみアクセス可能です。
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/create-profile', [UserController::class, 'showCreateProfile'])->name('profile.create');
    Route::post('/create-profile', [UserController::class, 'storeProfile'])->name('profile.store');
    Route::post('/upload-image', [UserController::class, 'uploadImage'])->name('uploadImage');
    
    // マイページと出品画面へのルートを追加
    Route::get('/mypage', [UserController::class, 'showMypage'])->name('mypage');
    Route::get('/sell', [ItemController::class, 'create'])->name('sell.form');

    // プロフィール編集画面のルートを追加
    Route::get('/mypage/profile', [UserController::class, 'showEditProfile'])->name('profile.edit');
    Route::post('/mypage/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::post('/mypage/profile/image', [UserController::class, 'uploadImage'])->name('profile.uploadImage');
});

/*
|--------------------------------------------------------------------------
| 認証必須のルート（プロフィール設定完了チェックも必要）
|--------------------------------------------------------------------------
| 以下のルートは全ての認証チェックが必要です：
| - ログイン済み（auth）
| - メール認証完了（verified）
| - プロフィール設定完了（profile.completed）
*/
Route::middleware(['auth', 'verified', 'profile.completed'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | 商品出品関連のルート
    |--------------------------------------------------------------------------
    | 商品の出品と画像アップロードに関するルートです。
    */
    Route::post('/sell/upload-image', [ItemController::class, 'uploadImage'])->name('sell.upload-image');
    Route::post('/sell/remove-image', [ItemController::class, 'removeImage'])->name('sell.remove-image');
    Route::post('/sell', [ItemController::class, 'store'])->name('item.store');

    /*
    |--------------------------------------------------------------------------
    | 商品購入関連のルート
    |--------------------------------------------------------------------------
    | 商品の購入プロセスに関するルートです。
    | 住所入力、確認、完了などの各ステップを含みます。
    */
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase');
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'showAddressForm'])->name('purchase.address');
    Route::put('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');
    Route::post('/purchase/confirm/{item_id}', [PurchaseController::class, 'confirm'])->name('purchase.confirm');
    Route::get('/purchase/{item_id}/complete', [PurchaseController::class, 'complete'])->name('purchase.complete');
    Route::get('/purchase/{item_id}/stripe-callback', [PurchaseController::class, 'handleStripeCallback'])->name('purchase.stripe.callback');
    Route::post('/purchase/{item_id}/payment-method', [PurchaseController::class, 'updatePaymentMethod'])->name('purchase.payment-method');

    /*
    |--------------------------------------------------------------------------
    | お気に入り関連のルート
    |--------------------------------------------------------------------------
    | 商品のお気に入り登録・解除に関するルートです。
    */
    Route::post('/favorites/{item_id}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/favorites/{item_id}/ajax', [FavoriteController::class, 'toggleAjax'])->name('favorites.toggle.ajax');

    /*
    |--------------------------------------------------------------------------
    | コメント関連のルート
    |--------------------------------------------------------------------------
    | 商品へのコメント投稿に関するルートです。
    */
    Route::post('/item/{item_id}/comment', [CommentController::class, 'store'])->name('comment.store');

    // 購入者チャット画面
    Route::get('/purchaser-chat', [ChatController::class, 'purchaser'])->name('purchaser.chat');

    // 出品者チャット画面
    Route::match(['get', 'post'], '/seller-chat', [ChatController::class, 'seller'])->name('seller.chat');

    // 取引中（チャット付き商品）タブ用ルート
    Route::get('/mypage/trade', [\App\Http\Controllers\ChatController::class, 'trade'])->name('mypage.trade');

    // チャット開始（チャットボタン押下時の保存用）
    Route::post('/chat/start', [ChatController::class, 'start'])->name('chat.start');

    // チャット送信
    Route::post('/chat/send/{item_id}', [ChatController::class, 'send'])->name('chat.send');
    // チャット編集（フォーム表示）
    Route::get('/chat/edit/{chat_id}', [ChatController::class, 'edit'])->name('chat.edit.get');
    // チャット編集（更新）
    Route::post('/chat/edit/{chat_id}', [ChatController::class, 'edit'])->name('chat.edit');
    // チャット削除
    Route::delete('/chat/delete/{chat_id}', [ChatController::class, 'delete'])->name('chat.delete');

    // 購入者による星評価（POST）
    Route::post('/purchaser-chat/rate/{item_id}', [ChatController::class, 'rate'])->name('purchaser.rate');
});