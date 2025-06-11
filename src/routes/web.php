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

// トップページ（商品一覧）
Route::get('/', [ItemController::class, 'index'])->name('items.index');
// 商品検索
Route::get('/search', [ItemController::class, 'search'])->name('items.search');
// 商品詳細
Route::get('/item/{id}', [ItemController::class, 'show'])->name('item.show');

/*
|--------------------------------------------------------------------------
| ユーザー登録関連のルート
|--------------------------------------------------------------------------
| 新規ユーザー登録のためのルートです。
| 認証は不要です。
*/

// ユーザー登録関連
Route::prefix('register')->group(function () {
    // 新規登録フォーム
    Route::get('/', [RegisterController::class, 'showRegisterForm'])->name('register.form');
    // 新規登録処理
    Route::post('/', [RegisterController::class, 'register'])->name('register');
});

/*
|--------------------------------------------------------------------------
| ログイン関連のルート
|--------------------------------------------------------------------------
| ユーザーのログイン・ログアウト処理のためのルートです。
| 認証は不要です。
*/

// ログイン関連
// ログインフォーム
Route::get('/login', [RegisterController::class, 'showLoginForm'])->name('login');
// ログイン処理
Route::post('/login', [RegisterController::class, 'login']);
// ログアウト
Route::post('/logout', [RegisterController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| メール認証関連のルート
|--------------------------------------------------------------------------
| メールアドレスの認証に関するルートです。
| authミドルウェアで保護されており、ログイン済みユーザーのみアクセス可能です。
*/

// メール認証関連
Route::prefix('verification')->middleware(['auth'])->group(function () {
    // 認証通知画面
    Route::get('/', function () {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('profile.create');
        }
        return view('verification');
    })->name('verification.notice');
    // メール認証リンク
    Route::get('/{id}/{hash}', [RegisterController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');
    // 認証メール再送信
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

// プロフィール設定関連
Route::middleware(['auth', 'verified'])->group(function () {
    // プロフィール作成フォーム
    Route::get('/create-profile', [UserController::class, 'showCreateProfile'])->name('profile.create');
    // プロフィール作成処理
    Route::post('/create-profile', [UserController::class, 'storeProfile'])->name('profile.store');
    // プロフィール画像アップロード
    Route::post('/upload-image', [UserController::class, 'uploadImage'])->name('uploadImage');
    // マイページ
    Route::get('/mypage', [UserController::class, 'showMypage'])->name('mypage');
    // 出品画面
    Route::get('/sell', [ItemController::class, 'create'])->name('sell.form');
    // プロフィール編集画面
    Route::get('/mypage/profile', [UserController::class, 'showEditProfile'])->name('profile.edit');
    // プロフィール更新
    Route::post('/mypage/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    // プロフィール画像更新
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

// 認証必須（プロフィール設定完了必須）
Route::middleware(['auth', 'verified', 'profile.completed'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | 商品出品関連のルート
    |--------------------------------------------------------------------------
    | 商品の出品と画像アップロードに関するルートです。
    */

    // 商品出品関連
    Route::post('/sell/upload-image', [ItemController::class, 'uploadImage'])->name('sell.upload-image'); // 画像アップロード
    Route::post('/sell/remove-image', [ItemController::class, 'removeImage'])->name('sell.remove-image'); // 画像削除
    Route::post('/sell', [ItemController::class, 'store'])->name('item.store'); // 商品出品

    /*
    |--------------------------------------------------------------------------
    | 商品購入関連のルート
    |--------------------------------------------------------------------------
    | 商品の購入プロセスに関するルートです。
    | 住所入力、確認、完了などの各ステップを含みます。
    */

    // 商品購入関連
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase'); // 購入画面
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'showAddressForm'])->name('purchase.address'); // 住所入力
    Route::put('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update'); // 住所更新
    Route::post('/purchase/confirm/{item_id}', [PurchaseController::class, 'confirm'])->name('purchase.confirm'); // 購入確認
    Route::get('/purchase/{item_id}/complete', [PurchaseController::class, 'complete'])->name('purchase.complete'); // 購入完了
    Route::get('/purchase/{item_id}/stripe-callback', [PurchaseController::class, 'handleStripeCallback'])->name('purchase.stripe.callback'); // Stripeコールバック
    Route::post('/purchase/{item_id}/payment-method', [PurchaseController::class, 'updatePaymentMethod'])->name('purchase.payment-method'); // 支払い方法変更

    /*
    |--------------------------------------------------------------------------
    | お気に入り関連のルート
    |--------------------------------------------------------------------------
    | 商品のお気に入り登録・解除に関するルートです。
    */

    // お気に入り関連
    Route::post('/favorites/{item_id}', [FavoriteController::class, 'toggle'])->name('favorites.toggle'); // お気に入り登録/解除
    Route::post('/favorites/{item_id}/ajax', [FavoriteController::class, 'toggleAjax'])->name('favorites.toggle.ajax'); // お気に入りAjax

    /*
    |--------------------------------------------------------------------------
    | コメント関連のルート
    |--------------------------------------------------------------------------
    | 商品へのコメント投稿に関するルートです。
    */

    // コメント関連
    Route::post('/item/{item_id}/comment', [CommentController::class, 'store'])->name('comment.store'); // コメント投稿

    // チャット関連
    Route::get('/purchaser-chat', [ChatController::class, 'purchaser'])->name('purchaser.chat'); // 購入者チャット
    Route::match(['get', 'post'], '/seller-chat', [ChatController::class, 'seller'])->name('seller.chat'); // 出品者チャット
    Route::get('/mypage/trade', [\App\Http\Controllers\ChatController::class, 'trade'])->name('mypage.trade'); // 取引中タブ
    Route::post('/chat/start', [ChatController::class, 'start'])->name('chat.start'); // チャット開始
    Route::post('/chat/send/{item_id}', [ChatController::class, 'send'])->name('chat.send'); // チャット送信
    Route::get('/chat/edit/{chat_id}', [ChatController::class, 'edit'])->name('chat.edit.get'); // チャット編集フォーム
    Route::post('/chat/edit/{chat_id}', [ChatController::class, 'edit'])->name('chat.edit'); // チャット編集更新
    Route::delete('/chat/delete/{chat_id}', [ChatController::class, 'delete'])->name('chat.delete'); // チャット削除
    // 星評価
    Route::post('/purchaser-chat/rate/{item_id}', [ChatController::class, 'rate'])->name('purchaser.rate'); // 購入者評価
    Route::post('/seller-chat/rate/{item_id}', [ChatController::class, 'sellerRate'])->name('seller.rate'); // 出品者評価
});