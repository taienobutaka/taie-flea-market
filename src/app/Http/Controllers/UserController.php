<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;

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
        $page = request('page', 'sell');
        $profile = $user->profile;

        // 出品した商品
        $sellingItems = Item::where('user_id', $user->id)->latest()->get();
        // 購入した商品
        $purchasedItems = Item::whereHas('purchases', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->latest()->get();

        // 共通の評価計算メソッドを使用
        $ratings = $this->calculateUserRatings($user);

        $items = $page === 'sell' ? $sellingItems : $purchasedItems;
        return view('mypage', [
            'user' => $user,
            'profile' => $profile,
            'items' => $items,
            'page' => $page,
            'ratingAvg' => $ratings['ratingAvg'],
            'ratingCount' => $ratings['ratingCount'],
            'ratingAvgBuyer' => $ratings['ratingAvgBuyer'],
            'ratingCountBuyer' => $ratings['ratingCountBuyer'],
        ]);
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
        \Log::info('UserController calculateUserRatings詳細', [
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
