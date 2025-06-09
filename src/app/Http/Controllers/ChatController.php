<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        $chat = \App\Models\Chat::where('item_id', $itemId)->where('user_id', '!=', $user->id)->first();
        $purchaser = $chat ? $chat->user : null;
        $purchaserProfile = $purchaser ? $purchaser->profile : null;
        // サイドバー用：自分が関わる全チャットの商品一覧（mypage.tradeと同じロジック）
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
        ]);
    }
    public function trade(Request $request)
    {
        $user = auth()->user();
        $profile = $user->profile;
        $itemId = $request->query('item_id');
        if ($itemId) {
            // チャットから遷移時は該当商品のみ表示
            $items = \App\Models\Item::where('id', $itemId)->get();
        } else {
            // 通常はチャットが存在する商品一覧
            $chatItemIds = \App\Models\Chat::where('user_id', $user->id)->pluck('item_id');
            $items = \App\Models\Item::whereIn('id', $chatItemIds)->get();
        }
        return view('mypage', [
            'items' => $items,
            'page' => 'trade',
            'profile' => $profile,
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
        // 既にチャットが存在する場合は重複登録しない
        // ここで空メッセージのチャットレコードは作成しない
        // 取引中タブへリダイレクト
        return redirect()->route('mypage.trade', ['item_id' => $itemId]);
    }
    public function send(Request $request, $item_id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        $user = auth()->user();
        \App\Models\Chat::create([
            'user_id' => $user->id,
            'item_id' => $item_id,
            'comment' => $request->input('comment'),
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
        return redirect()->route('purchaser.chat', ['item_id' => $item_id]);
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
}
