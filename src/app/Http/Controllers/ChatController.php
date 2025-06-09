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
            // ★評価済み商品を除外
            $ratedItemIds = \App\Models\Chat::whereIn('item_id', $chatItemIds)
                ->where('user_id', '!=', $user->id)
                ->whereNotNull('rating')
                ->pluck('item_id')
                ->unique();
            $items = \App\Models\Item::whereIn('id', $chatItemIds)
                ->whereNotIn('id', $ratedItemIds)
                ->get();
        }
        // ★出品者としての評価（購入者から自分への評価）
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
        // ★購入者としての評価（出品者から自分への評価）
        $buyerRatings = \App\Models\Chat::where('user_id', $user->id)
            ->whereNotNull('rating')
            ->pluck('rating');
        $ratingAvgBuyer = $buyerRatings->count() > 0 ? round($buyerRatings->avg()) : 0;
        $ratingCountBuyer = $buyerRatings->count();
        return view('mypage', [
            'items' => $items,
            'page' => 'trade',
            'profile' => $profile,
            'ratingAvg' => $ratingAvg,
            'ratingCount' => $ratingCount,
            'ratingAvgBuyer' => $ratingAvgBuyer,
            'ratingCountBuyer' => $ratingCountBuyer,
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
        $user = auth()->user();
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);
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
        return redirect()->route('purchaser.chat', ['item_id' => $item_id])->with('success', '評価を送信しました');
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
        // ★出品者としての評価（購入者から自分への評価）
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
        // ★購入者としての評価（出品者から自分への評価）
        $buyerRatings = \App\Models\Chat::where('user_id', $user->id)
            ->whereNotNull('rating')
            ->pluck('rating');
        $ratingAvgBuyer = $buyerRatings->count() > 0 ? round($buyerRatings->avg()) : 0;
        $ratingCountBuyer = $buyerRatings->count();
        return view('mypage', [
            'items' => $items,
            'page' => 'sell',
            'profile' => $profile,
            'ratingAvg' => $ratingAvg,
            'ratingCount' => $ratingCount,
            'ratingAvgBuyer' => $ratingAvgBuyer,
            'ratingCountBuyer' => $ratingCountBuyer,
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
        // ★評価済み商品を除外
        $purchasedItemIds = \App\Models\Item::whereHas('purchases', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->pluck('id');
        $ratedItemIds = \App\Models\Chat::whereIn('item_id', $purchasedItemIds)
            ->where('user_id', $user->id)
            ->whereNotNull('rating')
            ->pluck('item_id')
            ->unique();
        $items = \App\Models\Item::whereIn('id', $purchasedItemIds)
            ->whereNotIn('id', $ratedItemIds)
            ->latest()->get();
        // ★出品者としての評価（購入者から自分への評価）
        $allMyItemIds = \App\Models\Item::where('user_id', $user->id)->pluck('id');
        $ratings = collect();
        if ($allMyItemIds->count() > 0) {
            $ratings = \App\Models\Chat::whereIn('item_id', $allMyItemIds)
                ->where('user_id', '!=', $user->id)
                ->whereNotNull('rating')
                ->pluck('rating');
        }
        $ratingAvg = $ratings->count() > 0 ? round($ratings->avg()) : 0;
        $ratingCount = $ratings->count();

        // ★購入者としての評価（出品者から自分への評価）
        $buyerRatings = \App\Models\Chat::where('user_id', $user->id)
            ->whereNotNull('rating')
            ->pluck('rating');
        $ratingAvgBuyer = $buyerRatings->count() > 0 ? round($buyerRatings->avg()) : 0;
        $ratingCountBuyer = $buyerRatings->count();
        return view('mypage', [
            'items' => $items,
            'page' => 'buy',
            'profile' => $profile,
            'ratingAvg' => $ratingAvg,
            'ratingCount' => $ratingCount,
            'ratingAvgBuyer' => $ratingAvgBuyer,
            'ratingCountBuyer' => $ratingCountBuyer,
        ]);
    }
}
