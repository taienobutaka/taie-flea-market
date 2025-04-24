<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showCreateProfile()
    {
        return view('create-profile');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();

        if ($request->hasFile('image')) {
            // 古い画像を削除
            if ($profile && $profile->image_path) {
                Storage::delete('public/' . $profile->image_path);
            }

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            
            // 画像を保存
            $path = $image->storeAs('public/img/profiles', $imageName);
            
            if ($path) {
                $imagePath = 'img/profiles/' . $imageName;
                
                // セッションに画像パスを保存
                session(['imagePath' => $imagePath]);
                
                // プロフィールが存在しない場合は新規作成
                if (!$profile) {
                    $profile = new Profile();
                    $profile->user_id = $user->id;
                }
                
                $profile->image_path = $imagePath;
                $profile->save();
                
                return redirect()->back()->with('success', 'プロフィール画像が更新されました。');
            }
        }

        return redirect()->back()->with('error', '画像のアップロードに失敗しました。');
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

    /**
     * マイページを表示
     *
     * @return \Illuminate\View\View
     */
    public function showMypage()
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();
        return view('mypage', compact('user', 'profile'));
    }

    public function showEditProfile()
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();
        return view('profile', compact('user', 'profile'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'postcode' => 'required|string|max:10',
            'address' => 'required|string|max:255',
            'building_name' => 'nullable|string|max:255',
        ]);

        $profile = Profile::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'username' => $request->username,
                'postcode' => $request->postcode,
                'address' => $request->address,
                'building_name' => $request->building_name,
            ]
        );

        return redirect()->route('mypage')->with('success', 'プロフィールを更新しました。');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
