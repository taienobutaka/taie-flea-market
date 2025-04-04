<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function showCreateProfile()
    {
        return view('create-profile');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // 元のファイル名を取得
        $originalName = $request->file('image')->getClientOriginalName();

        // ファイル名の重複を避けるためにタイムスタンプを付加
        $fileName = time() . '_' . $originalName;

        // 画像を保存
        $path = $request->file('image')->storeAs('public/img/profiles', $fileName);
        $imagePath = str_replace('public/', '', $path);

        // セッションに保存
        session(['imagePath' => $imagePath]);

        // デバッグログ
        \Log::info('Session imagePath after upload: ' . session('imagePath'));

        return back()->with('imagePath', $imagePath);
    }

    public function storeProfile(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'postcode' => 'required|string|max:10',
            'address' => 'required|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'image_path' => 'nullable|string', // 画像パスのバリデーション
        ]);

        $imagePath = $request->image_path;

        Profile::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'username' => $request->username,
                'postcode' => $request->postcode,
                'address' => $request->address,
                'building_name' => $request->building_name,
                'image_path' => $imagePath,
            ]
        );

        return redirect('/')->with('success', 'プロフィールが更新されました。');
    }
}
