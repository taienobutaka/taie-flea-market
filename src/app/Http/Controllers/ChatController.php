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
        return view('purchaser', [
            'item' => $item,
            'seller' => $seller,
            'sellerProfile' => $sellerProfile,
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
}
