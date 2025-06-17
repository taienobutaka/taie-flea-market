<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Profile;
use App\Http\Requests\AddressRequest;
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

        \Log::info('購入画面表示', [
            'item_id' => $item_id,
            'session_payment_method' => session('payment_method'),
            'session_all' => session()->all(),
            'has_errors' => session()->has('errors'),
            'errors' => session('errors')
        ]);

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
        
        // セッションから支払い方法を取得
        $selectedPaymentMethod = session('payment_method');
        
        \Log::info('支払い方法の状態', [
            'selected_payment_method' => $selectedPaymentMethod,
            'is_empty' => empty($selectedPaymentMethod),
            'is_null' => is_null($selectedPaymentMethod),
            'is_string' => is_string($selectedPaymentMethod),
            'length' => is_string($selectedPaymentMethod) ? strlen($selectedPaymentMethod) : 0
        ]);
        
        // 支払い方法が空文字列の場合はnullに設定
        if ($selectedPaymentMethod === '') {
            $selectedPaymentMethod = null;
            session()->forget('payment_method');
            \Log::info('支払い方法をnullに設定');
        }
        
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

    public function updatePaymentMethod(Request $request, $item_id)
    {
        $validator = \Validator::make($request->all(), [
            'payment_method' => 'required|in:convenience,credit_card'
        ], [
            'payment_method.required' => '支払い方法を選択してください。',
            'payment_method.in' => '有効な支払い方法を選択してください。'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('purchase', ['item_id' => $item_id])
                ->withErrors($validator);
        }

        // 支払い方法をセッションに保存
        session(['payment_method' => $request->payment_method]);

        return redirect()->route('purchase', ['item_id' => $item_id]);
    }

    public function confirm(Request $request, $item_id)
    {
        try {
            // セッションから支払い方法を取得
            $paymentMethod = session('payment_method');

            // 支払い方法が未選択の場合
            if (empty($paymentMethod)) {
                $validator = \Validator::make([], []);
                $validator->errors()->add('payment_method', '支払い方法を選択してください。');
                return redirect()
                    ->route('purchase', ['item_id' => $item_id])
                    ->withErrors($validator);
            }

            // リクエストのバリデーション
            $validator = \Validator::make($request->all(), [
                'postcode' => 'required|string|max:8',
                'address' => 'required|string|max:255',
                'building_name' => 'nullable|string|max:255'
            ], [
                'postcode.required' => '郵便番号は必須です。',
                'address.required' => '住所は必須です。'
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->route('purchase', ['item_id' => $item_id])
                    ->withErrors($validator)
                    ->withInput();
            }

            $item = Item::findOrFail($item_id);
            $user = Auth::user();

            // 商品が売り切れていないか確認
            if ($item->status === 'sold') {
                return redirect()->route('item.show', ['id' => $item_id])
                    ->with('error', 'この商品は既に売り切れています。');
            }

            // 自分の出品商品でないか確認
            if ($item->user_id === $user->id) {
                return redirect()->route('item.show', ['id' => $item_id])
                    ->with('error', '自分の出品商品は購入できません。');
            }

            // ユーザーのプロフィール情報を取得
            $profile = Profile::where('user_id', $user->id)->first();
            if (!$profile || !$profile->postcode || !$profile->address) {
                return redirect()->route('purchase.address', ['item_id' => $item_id])
                    ->with('error', '購入前に住所情報を登録してください。');
            }

            // 以下、既存の購入処理を続ける...

            // Stripeのチェックアウトセッションを作成
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            // 金額を整数に変換（円からセントへ）
            $amount = (int)round($item->price);

            \Log::info('Stripeチェックアウトセッション作成開始', [
                'item_id' => $item->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod
            ]);

            // 購入情報を一時的に保存（pending状態）
            $purchase = Purchase::create([
                'item_id' => $item->id,
                'user_id' => $user->id,
                'payment_method' => $paymentMethod,
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
                            'description' => "テスト環境 - 支払い方法: " . ($paymentMethod === 'credit_card' ? 'クレジットカード' : 'コンビニ決済'),
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
                    'payment_method' => $paymentMethod,
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

                    // 購入直後にチャットレコードを必ず作成（購入者・出品者両方）
                    $buyerId = $purchase->user_id;
                    $sellerId = $item->user_id;
                    // 購入者のチャット
                    if (!\App\Models\Chat::where('item_id', $item->id)->where('user_id', $buyerId)->exists()) {
                        \App\Models\Chat::create([
                            'item_id' => $item->id,
                            'user_id' => $buyerId,
                            'comment' => null
                        ]);
                    }
                    // 出品者のチャット
                    if (!\App\Models\Chat::where('item_id', $item->id)->where('user_id', $sellerId)->exists()) {
                        \App\Models\Chat::create([
                            'item_id' => $item->id,
                            'user_id' => $sellerId,
                            'comment' => null
                        ]);
                    }

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