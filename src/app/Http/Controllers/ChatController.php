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
        if ($request->isMethod('post')) {
            // ここでメッセージ送信処理を実装（現状は画面表示のみ）
            // 例: $request->input('message');
        }
        return view('seller');
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
        $exists = \App\Models\Chat::where('user_id', $user->id)->where('item_id', $itemId)->exists();
        if (!$exists) {
            \App\Models\Chat::create([
                'user_id' => $user->id,
                'item_id' => $itemId,
            ]);
        }
        // 取引中タブへリダイレクト
        return redirect()->route('mypage.trade', ['item_id' => $itemId]);
    }
    public function send(Request $request, $item_id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        \App\Models\Chat::create([
            'user_id' => auth()->id(),
            'item_id' => $item_id,
            'comment' => $request->input('comment'),
        ]);
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
