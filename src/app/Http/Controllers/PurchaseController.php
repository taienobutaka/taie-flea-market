<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Profile;
use App\Http\Requests\PurchaseAddressRequest;
use App\Http\Requests\AddressRequest;
use App\Models\PaymentMethod;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PurchaseController extends Controller
{
    private const PAYMENT_METHODS = [
        'convenience' => 'コンビニ払い',
        'credit_card' => 'カード払い'
    ];

    /**
     * 商品購入画面を表示
     */
    public function show($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // 既に購入済みの商品かチェック
        if ($item->purchases()->exists()) {
            return redirect()
                ->route('item.show', ['id' => $item_id])
                ->with('error', 'この商品は既に購入されています。');
        }

        // 自分の出品した商品かチェック
        if ($item->user_id === $user->id) {
            return redirect()
                ->route('item.show', ['id' => $item_id])
                ->with('error', '自分が出品した商品は購入できません。');
        }

        $profile = Profile::where('user_id', Auth::id())->first();
        
        // セッションから支払い方法を取得（デフォルトは空）
        $selectedPaymentMethod = session('payment_method', '');
        
        return view('purchase', [
            'item' => $item,
            'profile' => $profile,
            'selectedPaymentMethod' => $selectedPaymentMethod
        ]);
    }

    public function showAddressForm($item_id)
    {
        $item = Item::findOrFail($item_id);
        $profile = Profile::where('user_id', Auth::id())->first();
        return view('address', compact('item', 'profile'));
    }

    public function updateAddress(AddressRequest $request, $item_id)
    {
        $validated = $request->validated();

        $profile = Profile::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'postcode' => $validated['postcode'],
                'address' => $validated['address'],
                'building_name' => $validated['building_name']
            ]
        );

        return redirect()->route('purchase', $item_id)
            ->with('success', '住所を更新しました。');
    }

    public function confirm(Request $request, $item_id)
    {
        try {
            \Log::info('購入確認処理開始', [
                'item_id' => $item_id,
                'request_data' => $request->all()
            ]);

            // リクエストのバリデーション
            $request->validate([
                'payment_method' => 'required|in:convenience,credit_card',
                'postcode' => 'required|string|max:8',
                'address' => 'required|string|max:255',
                'building_name' => 'nullable|string|max:255'
            ]);

            $item = Item::findOrFail($item_id);
            $user = Auth::user();

            \Log::info('購入処理開始', [
                'item_id' => $item->id,
                'user_id' => $user->id,
                'payment_method' => $request->payment_method
            ]);

            // 商品が売り切れていないか確認
            if ($item->status === 'sold') {
                \Log::warning('商品は既に売り切れています', [
                    'item_id' => $item->id,
                    'status' => $item->status
                ]);
                return redirect()->route('item.show', ['id' => $item_id])
                    ->with('error', 'この商品は既に売り切れています。');
            }

            // 自分の出品商品でないか確認
            if ($item->user_id === $user->id) {
                \Log::warning('自分の出品商品は購入できません', [
                    'item_id' => $item->id,
                    'user_id' => $user->id,
                    'seller_id' => $item->user_id
                ]);
                return redirect()->route('item.show', ['id' => $item_id])
                    ->with('error', '自分の出品商品は購入できません。');
            }

            // ユーザーのプロフィール情報を取得
            $profile = Profile::where('user_id', $user->id)->first();
            if (!$profile) {
                \Log::warning('プロフィール情報が存在しません', [
                    'user_id' => $user->id
                ]);
                return redirect()->route('purchase.address', ['item_id' => $item_id])
                    ->with('error', '購入前に住所情報を登録してください。');
            }

            // プロフィール情報の必須項目チェック
            if (!$profile->postcode || !$profile->address) {
                \Log::warning('プロフィール情報が不完全です', [
                    'user_id' => $user->id,
                    'has_postcode' => !empty($profile->postcode),
                    'has_address' => !empty($profile->address)
                ]);
                return redirect()->route('purchase.address', ['item_id' => $item_id])
                    ->with('error', '配送先住所の情報が不完全です。住所を更新してください。');
            }

            // Stripeのチェックアウトセッションを作成
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            // 金額を整数に変換（円からセントへ）
            $amount = (int)round($item->price);

            \Log::info('Stripeチェックアウトセッション作成開始', [
                'item_id' => $item->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'payment_method' => $request->payment_method
            ]);

            // 購入情報を一時的に保存（pending状態）
            $purchase = Purchase::create([
                'item_id' => $item->id,
                'user_id' => $user->id,
                'payment_method' => $request->payment_method,
                'amount' => $item->price,
                'status' => 'pending',
                'postcode' => $profile->postcode,
                'address' => $profile->address,
                'building_name' => $profile->building_name
            ]);

            \Log::info('購入情報を一時保存', [
                'purchase_id' => $purchase->id,
                'item_id' => $item->id,
                'user_id' => $user->id
            ]);

            $checkout_session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => [
                            'name' => $item->name,
                            'description' => "テスト環境 - 支払い方法: " . ($request->payment_method === 'credit_card' ? 'クレジットカード' : 'コンビニ決済'),
                        ],
                        'unit_amount' => $amount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('purchase.stripe.callback', ['item_id' => $item_id, 'purchase_id' => $purchase->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('purchase', ['item_id' => $item_id]),
                'metadata' => [
                    'item_id' => $item->id,
                    'user_id' => $user->id,
                    'purchase_id' => $purchase->id,
                    'payment_method' => $request->payment_method,
                ],
            ]);

            // 購入情報にStripeセッションIDを保存
            $purchase->update(['stripe_session_id' => $checkout_session->id]);

            \Log::info('Stripeチェックアウトセッション作成完了', [
                'session_id' => $checkout_session->id,
                'purchase_id' => $purchase->id,
                'checkout_url' => $checkout_session->url
            ]);

            // Stripeのチェックアウトページにリダイレクト
            if ($checkout_session && $checkout_session->url) {
                \Log::info('Stripeチェックアウトページへリダイレクト', [
                    'checkout_url' => $checkout_session->url
                ]);
                return redirect()->away($checkout_session->url);
            } else {
                throw new \Exception('StripeチェックアウトセッションのURLが取得できませんでした。');
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('バリデーションエラー: ' . json_encode($e->errors()));
            return redirect()->route('purchase', ['item_id' => $item_id])
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('購入処理エラー: ' . $e->getMessage());
            \Log::error('スタックトレース: ' . $e->getTraceAsString());
            return redirect()->route('purchase', ['item_id' => $item_id])
                ->with('error', '購入処理中にエラーが発生しました。: ' . $e->getMessage());
        }
    }

    public function handleStripeCallback(Request $request, $item_id)
    {
        try {
            \Log::info('Stripeコールバック処理開始', [
                'item_id' => $item_id,
                'session_id' => $request->session_id,
                'purchase_id' => $request->purchase_id
            ]);

            // Stripeのテスト環境用の公開キーを設定
            Stripe::setApiKey(config('services.stripe.secret'));

            $session = \Stripe\Checkout\Session::retrieve($request->session_id);
            
            if ($session->payment_status === 'paid') {
                DB::beginTransaction();
                try {
                    $purchase = Purchase::findOrFail($request->purchase_id);
                    $item = Item::lockForUpdate()->findOrFail($item_id);
                    
                    // 商品が既に売却済みでないことを確認
                    if ($item->status === 'sold') {
                        throw new \Exception('この商品は既に売却されています。');
                    }

                    // 購入情報を更新
                    $purchase->update([
                        'status' => 'completed'
                    ]);

                    // 商品のステータスを更新
                    $item->update(['status' => 'sold']);

                    DB::commit();

                    \Log::info('購入処理完了', [
                        'purchase_id' => $purchase->id,
                        'item_id' => $item->id,
                        'user_id' => Auth::id(),
                        'session_id' => $session->id
                    ]);

                    return redirect()->route('purchase.complete', ['item_id' => $item_id]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('トランザクション失敗', [
                        'error' => $e->getMessage(),
                        'item_id' => $item_id,
                        'purchase_id' => $request->purchase_id,
                        'session_id' => $request->session_id
                    ]);
                    throw $e;
                }
            }

            \Log::warning('決済未完了', [
                'session_id' => $request->session_id,
                'payment_status' => $session->payment_status
            ]);

            return redirect()->route('purchase', $item_id)
                ->with('error', '決済が完了していません。');

        } catch (\Exception $e) {
            \Log::error('Stripeコールバックエラー', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'item_id' => $item_id,
                'session_id' => $request->session_id ?? null,
                'purchase_id' => $request->purchase_id ?? null
            ]);

            return redirect()->route('purchase', $item_id)
                ->with('error', '決済処理中にエラーが発生しました。: ' . $e->getMessage());
        }
    }

    public function complete($item_id)
    {
        $purchase = Purchase::where('item_id', $item_id)
            ->where('user_id', Auth::id())
            ->latest()
            ->firstOrFail();
            
        // 商品詳細画面にリダイレクト
        return redirect()->route('item.show', $item_id)
            ->with('success', '購入が完了しました。');
    }

    /**
     * 支払い方法を更新
     */
    public function updatePaymentMethod(Request $request, $item_id)
    {
        $request->validate([
            'payment_method' => 'required|in:convenience,credit_card'
        ]);

        // 支払い方法をセッションに保存
        session(['payment_method' => $request->payment_method]);

        \Log::info('支払い方法を更新', [
            'item_id' => $item_id,
            'payment_method' => $request->payment_method
        ]);

        return redirect()->route('purchase', ['item_id' => $item_id]);
    }

    /*
     * テスト用カード情報
     * 
     * 成功するカード:
     * - 番号: 4242 4242 4242 4242
     * - 有効期限: 未来の任意の日付
     * - CVC: 任意の3桁
     * - 郵便番号: 任意の数字
     * 
     * 失敗するカード:
     * - 番号: 4000 0000 0000 0002
     * - 有効期限: 未来の任意の日付
     * - CVC: 任意の3桁
     * - 郵便番号: 任意の数字
     */
} 