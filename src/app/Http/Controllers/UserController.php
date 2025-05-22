<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
use App\Models\Purchase;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showCreateProfile()
    {
        $user = Auth::user();
        
        // プロフィールが既に設定されている場合は商品一覧画面にリダイレクト
        if ($user->profile()->exists()) {
            return redirect('/');
        }

        return view('create-profile');
    }

    public function uploadImage(Request $request)
    {
        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                // デバッグ情報をログに記録
                \Log::info('Image upload debug:', [
                    'original_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getMimeType(),
                    'size' => $image->getSize(),
                    'extension' => $image->getClientOriginalExtension()
                ]);

                // 既存の画像を削除
                if (session('profile_image')) {
                    $oldImagePath = storage_path('app/public/' . session('profile_image'));
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // 画像を保存
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('img/profiles', $imageName, 'public');

                if ($path) {
                    // ファイルのパーミッションを設定
                    $fullPath = storage_path('app/public/' . $path);
                    chmod($fullPath, 0644);
                    
                    // セッションに画像パスを保存
                    session(['profile_image' => $path]);

                    // 保存後のデバッグ情報
                    \Log::info('Image saved debug:', [
                        'saved_path' => $fullPath,
                        'exists' => file_exists($fullPath),
                        'is_readable' => is_readable($fullPath),
                        'permissions' => substr(sprintf('%o', fileperms($fullPath)), -4),
                        'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                        'url' => '/storage/' . $path
                    ]);

                    return redirect()->back()->with('success', '画像が正常にアップロードされました。');
                }
            }
        } catch (\Exception $e) {
            \Log::error('Image upload error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', '画像のアップロード中にエラーが発生しました。');
        }

        return redirect()->back()->with('error', '画像が選択されていません。');
    }

    public function storeProfile(AddressRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        // プロフィールを作成
        $profile = new Profile();
        $profile->user_id = $user->id;
        $profile->username = $validated['username'];
        $profile->postcode = $validated['postcode'];
        $profile->address = $validated['address'];
        $profile->building_name = $validated['building_name'];
        
        // セッションから画像パスを取得
        if (session()->has('profile_image')) {
            $profile->image_path = session('profile_image');
            session()->forget('profile_image'); // セッションから削除
        }

        $profile->save();

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
        $page = request('page', 'sell');  // tabをpageに変更
        $profile = $user->profile;
        
        // 出品した商品を取得
        $sellingItems = Item::where('user_id', $user->id)
            ->latest()
            ->get();
            
        // 購入した商品を取得（purchasesテーブルから）
        $purchasedItems = Item::whereHas('purchases', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->latest()->get();

        // アクティブなページに応じて表示する商品を選択
        $items = $page === 'sell' ? $sellingItems : $purchasedItems;
        
        return view('mypage', [
            'user' => $user,
            'profile' => $profile,
            'items' => $items,
            'page' => $page  // activeTabをpageに変更
        ]);
    }

    public function showEditProfile()
    {
        $user = Auth::user();
        
        // デバッグ情報をログに記録
        \Log::info('Profile edit access attempt:', [
            'user_id' => $user->id,
            'email' => $user->email,
            'email_verified' => $user->hasVerifiedEmail(),
            'has_profile' => $user->profile()->exists()
        ]);

        if (!$user->hasVerifiedEmail()) {
            \Log::warning('Unverified user attempted to access profile edit', [
                'user_id' => $user->id
            ]);
            return redirect()->route('verification.notice');
        }

        $profile = Profile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            \Log::warning('User without profile attempted to access profile edit', [
                'user_id' => $user->id
            ]);
            return redirect()->route('profile.create');
        }

        return view('profile', compact('user', 'profile'));
    }

    public function updateProfile(AddressRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        $profileData = [
            'username' => $validated['username'],
            'postcode' => $validated['postcode'],
            'address' => $validated['address'],
            'building_name' => $validated['building_name'],
        ];

        // セッションから画像パスを取得
        if (session()->has('profile_image')) {
            $profileData['image_path'] = session('profile_image');
            session()->forget('profile_image'); // セッションから削除
        }

        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            $profileData
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

    public function showSellForm()
    {
        $user = Auth::user();
        
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('login');
        }

        return view('sell');
    }
}
