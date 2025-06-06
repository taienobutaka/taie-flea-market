<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function purchaser()
    {
        return view('purchaser');
    }
    public function seller(Request $request)
    {
        if ($request->isMethod('post')) {
            // ここでメッセージ送信処理を実装（現状は画面表示のみ）
            // 例: $request->input('message');
        }
        return view('seller');
    }
}
