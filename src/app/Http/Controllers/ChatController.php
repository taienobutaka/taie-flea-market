<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ChatSendRequest;

class ChatController extends Controller
{
    public function purchaser(Request $request)
    {
        $itemId = $request->query('item_id');
        $item = \App\Models\Item::with('user.profile')->find($itemId);
        $seller = $item ? $item->user : null;
        $sellerProfile = $seller ? $seller->profile : null;
        // チャット一覧（この商品のチャット）
        $chats = \App\Models\Chat::with('user.profile')->where('item_id', $itemId)->orderBy('created_at')->get();
        // サイドバー用：ユーザーが関わる全チャットの商品一覧
        $user = auth()->user();
        $sidebarItemIds = \App\Models\Chat::where('user_id', $user->id)->pluck('item_id')->unique();
        $sidebarItems = \App\Models\Item::whereIn('id', $sidebarItemIds)->get();
        // デバッグログ追加
        \Log::debug('purchaser sidebar debug', [
            'user_id' => $user ? $user->id : null,
            'sidebarItemIds' => $sidebarItemIds,
            'sidebarItems_count' => $sidebarItems->count(),
            'sidebarItems' => $sidebarItems->pluck('id')->toArray(),
        ]);
        return view('purchaser', [
            'item' => $item,
            'seller' => $seller,
            'sellerProfile' => $sellerProfile,
            'chats' => $chats,
            'sidebarItems' => $sidebarItems,
        ]);
    }
    public function seller(Request $request)
    {
        $itemId = $request->query('item_id');
        $item = \App\Models\Item::with('user.profile')->find($itemId);
        $user = auth()->user();
        // ★購入者がratingをつけたらtrue（コメントや画像の有無は問わない）
        $purchaserChat = \App\Models\Chat::where('item_id', $itemId)
            ->where('user_id', '!=', $item->user_id)
            ->whereNotNull('rating')
            ->orderBy('id', 'asc')
            ->first();
        $purchaser = $purchaserChat ? $purchaserChat->user : null;
        $purchaserProfile = $purchaser ? $purchaser->profile : null;
        $purchaserRated = $purchaserChat ? true : false;
        
        // 出品者が購入者へ評価したかチェック
        $sellerRated = \App\Models\Chat::where('item_id', $itemId)
            ->where('user_id', $user->id)
            ->whereNotNull('rating')
            ->exists();
        
        $chatItemIds = \App\Models\Chat::where('user_id', $user->id)->pluck('item_id');
        $sidebarItems = \App\Models\Item::whereIn('id', $chatItemIds)->get();
        // チャット一覧
        $chats = \App\Models\Chat::with('user.profile')->where('item_id', $itemId)->orderBy('created_at')->get();
        return view('seller', [
            'item' => $item,
            'purchaser' => $purchaser,
            'purchaserProfile' => $purchaserProfile,
            'chats' => $chats,
            'sidebarItems' => $sidebarItems,
            'purchaserRated' => $purchaserRated,
            'sellerRated' => $sellerRated,
        ]);
    }
    public function trade(Request $request)
    {
        $user = auth()->user();
        $profile = $user->profile;
        $itemId = $request->query('item_id');
        if ($itemId) {
            $items = \App\Models\Item::where('id', $itemId)->get();
        } else {
            $chatItemIds = \App\Models\Chat::where('user_id', $user->id)->pluck('item_id');
            
            // 出品者の場合：自分が購入者へ評価した商品のみを除外
            // 購入者の場合：自分が出品者へ評価した商品のみを除外
            $ratedItemIds = collect();
            foreach ($chatItemIds as $chatItemId) {
                $item = \App\Models\Item::find($chatItemId);
                if ($item) {
                    if ($user->id === $item->user_id) {
                        // 出品者の場合：自分が購入者へ評価したかチェック
                        $purchaserId = \App\Models\Chat::where('item_id', $chatItemId)
                            ->where('user_id', '!=', $user->id)
                            ->orderByDesc('id')
                            ->value('user_id');
                        if ($purchaserId) {
                            $sellerRated = \App\Models\Chat::where('item_id', $chatItemId)
                                ->where('user_id', $user->id)
                                ->whereNotNull('rating')
                                ->exists();
                            if ($sellerRated) {
                                $ratedItemIds->push($chatItemId);
                            }
                        }
                    } else {
                        // 購入者の場合：自分が出品者へ評価したかチェック
                        $buyerRated = \App\Models\Chat::where('item_id', $chatItemId)
                            ->where('user_id', $user->id)
                            ->whereNotNull('rating')
                            ->exists();
                        if ($buyerRated) {
                            $ratedItemIds->push($chatItemId);
                        }
                    }
                }
            }
            
            $items = \App\Models\Item::whereIn('id', $chatItemIds)
                ->whereNotIn('id', $ratedItemIds)
                ->get();
        }
        
        // 取引中の商品数を計算
        $tradeCount = \App\Models\Chat::where('user_id', $user->id)->pluck('item_id')->unique()->count();
        
        // 共通の評価計算を使用
        $ratings = $this->calculateUserRatings($user);
        
        // デバッグ情報をログに記録
        \Log::info('tradeメソッド評価計算', [
            'user_id' => $user->id,
            'user_name' => $profile->username ?? $user->name,
            'ratings' => $ratings,
            'page' => 'trade'
        ]);
        
        // 取引中タブ用：各商品ごとの「相手からの」メッセージ件数（空コメントは除外）を取得
        $messageCounts = [];
        $totalReceived = 0;
        if ($items && $items->count()) {
            foreach ($items as $item) {
                // 自分以外のユーザーからの「空でない」メッセージ件数
                $count = \App\Models\Chat::where('item_id', $item->id)
                    ->where('user_id', '!=', $user->id)
                    ->whereNotNull('comment')
                    ->where('comment', '!=', '')
                    ->count();
                $messageCounts[$item->id] = $count;
                $totalReceived += $count;
            }
        }
        return view('mypage', [
            'items' => $items,
            'page' => 'trade',
            'profile' => $profile,
            'ratingAvg' => $ratings['ratingAvg'],
            'ratingCount' => $ratings['ratingCount'],
            'ratingAvgBuyer' => $ratings['ratingAvgBuyer'],
            'ratingCountBuyer' => $ratings['ratingCountBuyer'],
            'messageCounts' => $messageCounts,
            'totalReceived' => $totalReceived,
            'tradeCount' => $tradeCount,
        ]);
    }
    /**
     * チャット開始（チャットボタン押下時）
     */
    public function start(Request $request)
    {
        $user = auth()->user();
        $itemId = $request->input('item_id');
        if (!$itemId || !$user) {
            return redirect()->back()->with('error', '不正なリクエストです');
        }
        // 自分のチャットがなければ作成
        $exists = \App\Models\Chat::where('item_id', $itemId)->where('user_id', $user->id)->exists();
        if (!$exists) {
            $chat = new \App\Models\Chat();
            $chat->item_id = $itemId;
            $chat->user_id = $user->id;
            $chat->comment = null;
            $chat->save();
        }
        // 購入者がチャットを開始した場合のみ、出品者にも空チャットレコードを作成
        $item = \App\Models\Item::find($itemId);
        if ($item && $user->id !== $item->user_id) {
            $sellerId = $item->user_id;
            $sellerExists = \App\Models\Chat::where('item_id', $itemId)->where('user_id', $sellerId)->exists();
            if (!$sellerExists) {
                $sellerChat = new \App\Models\Chat();
                $sellerChat->item_id = $itemId;
                $sellerChat->user_id = $sellerId;
                $sellerChat->comment = null;
                $sellerChat->save();
            }
        }
        // 取引中タブへリダイレクト
        return redirect()->route('mypage.trade', ['item_id' => $itemId]);
    }
    public function send(ChatSendRequest $request, $item_id)
    {
        $user = auth()->user();
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('img/chats', $fileName, 'public');
            $imagePath = $path;
        }
        \App\Models\Chat::create([
            'user_id' => $user->id,
            'item_id' => $item_id,
            'comment' => $request->input('comment'),
            'image_path' => $imagePath,
        ]);
        $item = \App\Models\Item::find($item_id);
        if ($item) {
            // 出品者ID
            $sellerId = $item->user_id;
            $purchaserId = null;
            if ($user->id === $sellerId) {
                $purchaserId = \App\Models\Chat::where('item_id', $item_id)->where('user_id', '!=', $sellerId)->orderByDesc('id')->value('user_id');
            } else {
                $purchaserId = $sellerId;
            }
            // 相手側にチャットレコードがなければ作成しない（空メッセージ禁止）
            // 何もしない
        }
        // 送信者が出品者ならseller.chat、購入者ならpurchaser.chatにリダイレクト
        if ($item && $user->id === $item->user_id) {
            return redirect()->route('seller.chat', ['item_id' => $item_id]);
        } else {
            return redirect()->route('purchaser.chat', ['item_id' => $item_id]);
        }
    }

    public function edit(Request $request, $chat_id)
    {
        $chat = \App\Models\Chat::findOrFail($chat_id);
        if ($chat->user_id !== auth()->id()) {
            abort(403);
        }
        if ($request->isMethod('post')) {
            $request->validate([
                'comment' => 'required|string|max:1000',
            ]);
            $chat->comment = $request->input('comment');
            $chat->save();
            return redirect()->route('purchaser.chat', ['item_id' => $chat->item_id]);
        }
        // 編集フォーム表示
        $item = \App\Models\Item::with('user.profile')->find($chat->item_id);
        $seller = $item ? $item->user : null;
        $sellerProfile = $seller ? $seller->profile : null;
        $chats = \App\Models\Chat::with('user.profile')->where('item_id', $chat->item_id)->orderBy('created_at')->get();
        $user = auth()->user();
        $sidebarItemIds = \App\Models\Chat::where('user_id', $user->id)->pluck('item_id')->unique();
        $sidebarItems = \App\Models\Item::whereIn('id', $sidebarItemIds)->get();
        return view('purchaser', [
            'item' => $item,
            'seller' => $seller,
            'sellerProfile' => $sellerProfile,
            'chats' => $chats,
            'sidebarItems' => $sidebarItems,
            'editChat' => $chat,
        ]);
    }

    public function delete($chat_id)
    {
        $chat = \App\Models\Chat::findOrFail($chat_id);
        if ($chat->user_id !== auth()->id()) {
            abort(403);
        }
        $item_id = $chat->item_id;
        $chat->delete();
        return redirect()->route('purchaser.chat', ['item_id' => $item_id]);
    }
    /**
     * 購入者チャット画面の星評価保存
     */
    public function rate(Request $request, $item_id)
    {
        $user = auth()->user()->load('profile');
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);
        
        // 商品と出品者情報を取得（プロフィールも含む）
        $item = \App\Models\Item::with('user.profile')->find($item_id);
        if (!$item) {
            return redirect()->back()->with('error', '商品が見つかりません');
        }
        
        $seller = $item->user;
        
        // 既存の自分のチャットレコードを取得（item_id, user_idで1件）
        $chat = \App\Models\Chat::where('item_id', $item_id)->where('user_id', $user->id)->first();
        if (!$chat) {
            // チャットがなければ新規作成（コメント・画像なし、ratingのみ）
            $chat = new \App\Models\Chat();
            $chat->user_id = $user->id;
            $chat->item_id = $item_id;
        }
        $chat->rating = $request->input('rating');
        $chat->save();
        
        // 出品者へメール通知を送信
        try {
            \Mail::to($seller->email)->send(new \App\Mail\RatingNotification($user, $seller, $item, $request->input('rating')));
            \Log::info('評価通知メール送信完了', [
                'purchaser_id' => $user->id,
                'purchaser_name' => $user->profile->username ?? $user->name,
                'seller_id' => $seller->id,
                'seller_name' => $seller->profile->username ?? $seller->name,
                'item_id' => $item_id,
                'rating' => $request->input('rating')
            ]);
        } catch (\Exception $e) {
            \Log::error('評価通知メール送信エラー', [
                'error' => $e->getMessage(),
                'purchaser_id' => $user->id,
                'seller_id' => $seller->id,
                'item_id' => $item_id
            ]);
        }
        
        return redirect()->route('items.index')->with('success', '評価を送信しました');
    }

    /**
     * 出品者チャット画面の星評価保存
     */
    public function sellerRate(Request $request, $item_id)
    {
        $user = auth()->user();
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);
        
        // デバッグ情報をログに記録
        \Log::info('出品者評価保存開始', [
            'user_id' => $user->id,
            'item_id' => $item_id,
            'rating' => $request->input('rating')
        ]);
        
        // 既存の自分のチャットレコードを取得（item_id, user_idで1件）
        $chat = \App\Models\Chat::where('item_id', $item_id)->where('user_id', $user->id)->first();
        if (!$chat) {
            // チャットがなければ新規作成（コメント・画像なし、ratingのみ）
            $chat = new \App\Models\Chat();
            $chat->user_id = $user->id;
            $chat->item_id = $item_id;
            \Log::info('新しいチャットレコードを作成', [
                'user_id' => $user->id,
                'item_id' => $item_id
            ]);
        } else {
            \Log::info('既存のチャットレコードを更新', [
                'chat_id' => $chat->id,
                'user_id' => $user->id,
                'item_id' => $item_id,
                'current_rating' => $chat->rating
            ]);
        }
        $chat->rating = $request->input('rating');
        $chat->save();
        
        // 保存後の確認
        \Log::info('出品者評価保存完了', [
            'chat_id' => $chat->id,
            'user_id' => $chat->user_id,
            'item_id' => $chat->item_id,
            'rating' => $chat->rating,
            'saved_at' => $chat->updated_at
        ]);
        
        return redirect()->route('items.index')->with('success', '評価を送信しました');
    }
    /**
     * 共通の評価計算メソッド
     */
    private function calculateUserRatings($user)
    {
        // 出品者としての評価（購入者から自分への評価）
        $itemIds = \App\Models\Item::where('user_id', $user->id)->pluck('id');
        $ratingAvg = 0;
        $ratingCount = 0;
        if ($itemIds->count() > 0) {
            $ratings = \App\Models\Chat::whereIn('item_id', $itemIds)
                ->where('user_id', '!=', $user->id)
                ->whereNotNull('rating')
                ->pluck('rating');
            if ($ratings->count() > 0) {
                $ratingAvg = round($ratings->avg());
                $ratingCount = $ratings->count();
            }
        }
        
        // 購入者としての評価（出品者から自分への評価）
        // チャットに関わった商品の出品者からの評価を計算
        $chatItemIds = \App\Models\Chat::where('user_id', $user->id)->pluck('item_id')->unique();
        $buyerRatings = collect();
        if ($chatItemIds->count() > 0) {
            $chatItems = \App\Models\Item::whereIn('id', $chatItemIds)->get();
            foreach ($chatItems as $item) {
                // 各商品について、出品者（item->user_id）が購入者（$user->id）を評価した内容を取得
                $rating = \App\Models\Chat::where('item_id', $item->id)
                    ->where('user_id', $item->user_id) // 出品者の評価
                    ->whereNotNull('rating')
                    ->value('rating');
                
                if ($rating) {
                    $buyerRatings->push($rating);
                }
            }
        }
        
        $ratingAvgBuyer = $buyerRatings->count() > 0 ? round($buyerRatings->avg()) : 0;
        $ratingCountBuyer = $buyerRatings->count();
        
        // デバッグ情報をログに記録
        \Log::info('calculateUserRatings詳細', [
            'user_id' => $user->id,
            'item_ids' => $itemIds->toArray(),
            'ratings' => $ratings ?? collect(),
            'rating_avg' => $ratingAvg,
            'rating_count' => $ratingCount,
            'chat_item_ids' => $chatItemIds->toArray(),
            'buyer_ratings' => $buyerRatings->toArray(),
            'rating_avg_buyer' => $ratingAvgBuyer,
            'rating_count_buyer' => $ratingCountBuyer
        ]);
        
        return [
            'ratingAvg' => $ratingAvg,
            'ratingCount' => $ratingCount,
            'ratingAvgBuyer' => $ratingAvgBuyer,
            'ratingCountBuyer' => $ratingCountBuyer
        ];
    }

    // 出品した商品タブ
    public function sell(Request $request)
    {
        $user = auth()->user();
        $profile = $user->profile;
        // ★評価済み商品を除外
        $allMyItemIds = \App\Models\Item::where('user_id', $user->id)->pluck('id');
        $ratedItemIds = \App\Models\Chat::whereIn('item_id', $allMyItemIds)
            ->where('user_id', '!=', $user->id)
            ->whereNotNull('rating')
            ->pluck('item_id')
            ->unique();
        $items = \App\Models\Item::where('user_id', $user->id)
            ->whereNotIn('id', $ratedItemIds)
            ->latest()->get();
        
        // 出品した商品数を計算
        $sellCount = \App\Models\Item::where('user_id', $user->id)->count();
        
        // メッセージ件数を計算（取引中タブと同じロジック）
        $chatItemIds = \App\Models\Chat::where('user_id', $user->id)->pluck('item_id');
        $messageCounts = [];
        $totalReceived = 0;
        if ($chatItemIds->count() > 0) {
            $chatItems = \App\Models\Item::whereIn('id', $chatItemIds)->get();
            foreach ($chatItems as $item) {
                // 自分以外のユーザーからの「空でない」メッセージ件数
                $count = \App\Models\Chat::where('item_id', $item->id)
                    ->where('user_id', '!=', $user->id)
                    ->whereNotNull('comment')
                    ->where('comment', '!=', '')
                    ->count();
                $messageCounts[$item->id] = $count;
                $totalReceived += $count;
            }
        }
        
        // 共通の評価計算を使用
        $ratings = $this->calculateUserRatings($user);
        
        // デバッグ情報をログに記録
        \Log::info('sellメソッド評価計算', [
            'user_id' => $user->id,
            'user_name' => $profile->username ?? $user->name,
            'ratings' => $ratings,
            'page' => 'sell'
        ]);
        
        return view('mypage', [
            'items' => $items,
            'page' => 'sell',
            'profile' => $profile,
            'ratingAvg' => $ratings['ratingAvg'],
            'ratingCount' => $ratings['ratingCount'],
            'ratingAvgBuyer' => $ratings['ratingAvgBuyer'],
            'ratingCountBuyer' => $ratings['ratingCountBuyer'],
            'sellCount' => $sellCount,
            'totalReceived' => $totalReceived,
        ]);
    }

    // 購入した商品タブ
    public function buy(Request $request)
    {
        $user = auth()->user();
        $profile = $user->profile;
        if (!$profile) {
            $profile = (object)[
                'username' => $user->name ?? 'ユーザー名',
                'user_id' => (int)$user->id
            ];
        } else {
            $profile->user_id = (int)$user->id;
        }
        
        // ★チャットに関わった商品を取得（購入チェックを削除）
        $chatItemIds = \App\Models\Chat::where('user_id', $user->id)->pluck('item_id')->unique();
        
        // ★評価済み商品を除外
        $ratedItemIds = \App\Models\Chat::whereIn('item_id', $chatItemIds)
            ->where('user_id', $user->id)
            ->whereNotNull('rating')
            ->pluck('item_id')
            ->unique();
        
        $items = \App\Models\Item::whereIn('id', $chatItemIds)
            ->whereNotIn('id', $ratedItemIds)
            ->latest()->get();
        
        // チャットに関わった商品数を計算
        $buyCount = $chatItemIds->count();
        
        // メッセージ件数を計算（取引中タブと同じロジック）
        $messageCounts = [];
        $totalReceived = 0;
        if ($chatItemIds->count() > 0) {
            $chatItems = \App\Models\Item::whereIn('id', $chatItemIds)->get();
            foreach ($chatItems as $item) {
                // 自分以外のユーザーからの「空でない」メッセージ件数
                $count = \App\Models\Chat::where('item_id', $item->id)
                    ->where('user_id', '!=', $user->id)
                    ->whereNotNull('comment')
                    ->where('comment', '!=', '')
                    ->count();
                $messageCounts[$item->id] = $count;
                $totalReceived += $count;
            }
        }
        
        // 共通の評価計算を使用
        $ratings = $this->calculateUserRatings($user);
        
        // デバッグ情報をログに記録
        \Log::info('buyメソッド評価計算', [
            'user_id' => $user->id,
            'user_name' => $profile->username ?? $user->name,
            'ratings' => $ratings,
            'page' => 'buy'
        ]);
        
        return view('mypage', [
            'items' => $items,
            'page' => 'buy',
            'profile' => $profile,
            'ratingAvg' => $ratings['ratingAvg'],
            'ratingCount' => $ratings['ratingCount'],
            'ratingAvgBuyer' => $ratings['ratingAvgBuyer'],
            'ratingCountBuyer' => $ratings['ratingCountBuyer'],
            'buyCount' => $buyCount,
            'totalReceived' => $totalReceived,
        ]);
    }
}
