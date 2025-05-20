<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Http\Requests\ExhibitionRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\CommentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    /**
     * 商品一覧を表示
     */
    public function index(Request $request)
    {
        try {
            $view = $request->input('view', 'recommended');
            $search = $request->input('search');
            $user = Auth::user();
            $isGuest = !Auth::check();
            
            \Log::info('商品一覧表示開始', [
                'view' => $view,
                'search' => $search,
                'user_id' => $user ? $user->id : '未ログイン',
                'is_authenticated' => !$isGuest
            ]);
            
            if ($view === 'favorites') {
                if ($isGuest) {
                    // 未ログインユーザーの場合は空の商品リストを表示
                    return view('items', ['items' => collect(), 'view' => 'favorites', 'search' => $search]);
                }
                // お気に入り商品を取得（自分が出品した商品は除外）
                \Log::info('お気に入り商品取得開始', [
                    'user_id' => $user->id,
                    'favorites_count' => $user->favorites()->count()
                ]);

                // お気に入り登録されている商品IDを取得
                $favoriteItemIds = $user->favorites()->pluck('item_id');
                \Log::info('お気に入り商品ID一覧', [
                    'item_ids' => $favoriteItemIds->toArray()
                ]);

                // お気に入り商品を取得（自分が出品した商品は除外）
                $query = Item::whereIn('id', $favoriteItemIds)
                    ->where('user_id', '!=', $user->id);  // 自分が出品した商品を除外

                // 検索クエリがある場合は商品名で部分一致検索
                if ($search) {
                    // 検索クエリを個別の文字に分解
                    $searchTerms = str_split($search);
                    $query->where(function($q) use ($searchTerms) {
                        // 最初の文字は必須
                        $q->where('name', 'LIKE', '%' . $searchTerms[0] . '%');
                        // 残りの文字はOR条件で追加
                        for ($i = 1; $i < count($searchTerms); $i++) {
                            $q->orWhere('name', 'LIKE', '%' . $searchTerms[$i] . '%');
                        }
                    });
                }

                $items = $query->with(['purchases', 'favorites' => function($query) use ($user) {
                    $query->where('user_id', $user->id);
                }])
                ->latest()
                ->get();

                \Log::info('お気に入り商品取得結果', [
                    'count' => $items->count(),
                    'user_id' => $user->id,
                    'excluded_own_items' => true,
                    'favorite_item_ids' => $favoriteItemIds->toArray(),
                    'search_query' => $search
                ]);

                // 購入済み商品のステータスを一括更新
                DB::transaction(function () use ($items) {
                    foreach ($items as $item) {
                        if ($item->purchases()->exists() && $item->status !== 'sold') {
                            $item->status = 'sold';
                            $item->save();
                            \Log::info('お気に入り商品のステータスを更新:', [
                                'item_id' => $item->id,
                                'name' => $item->name,
                                'status' => 'sold',
                                'seller_id' => $item->user_id,
                                'has_purchases' => $item->purchases()->exists()
                            ]);
                        }
                    }
                });

                // 取得された商品の詳細情報をログに出力
                foreach ($items as $item) {
                    \Log::info('お気に入り商品詳細:', [
                        'item_id' => $item->id,
                        'name' => $item->name,
                        'seller_id' => $item->user_id,
                        'is_own_item' => $item->user_id === $user->id,
                        'favorite_count' => $item->favorites()->count(),
                        'is_favorited_by_user' => $item->isFavoritedBy($user)
                    ]);
                }
            } else {
                // 通常の商品一覧を取得
                $query = Item::with(['purchases', 'favorites' => function($query) use ($user) {
                    if ($user) {
                        $query->where('user_id', $user->id);
                    }
                }]);

                \Log::info('検索前のクエリ状態', [
                    'search' => $search,
                    'user_id' => $user ? $user->id : null,
                    'base_query_sql' => $query->toSql(),
                    'base_query_bindings' => $query->getBindings()
                ]);

                // 検索クエリがある場合は商品名で部分一致検索
                if ($search) {
                    // 検索クエリを正規化（全角スペースを半角に変換、連続するスペースを1つに）
                    $normalizedSearch = preg_replace('/\s+/', ' ', mb_convert_kana($search, 's'));
                    
                    \Log::info('検索条件の構築開始', [
                        'original_search' => $search,
                        'normalized_search' => $normalizedSearch
                    ]);

                    // 検索クエリを単語に分割
                    $words = array_filter(explode(' ', $normalizedSearch));
                    \Log::info('分割された検索単語', ['words' => $words]);

                    // 各単語で部分一致検索を実行
                    $query->where(function($q) use ($words) {
                        foreach ($words as $word) {
                            $q->orWhere('name', 'LIKE', '%' . $word . '%');
                        }
                    });

                    \Log::info('検索条件適用後のクエリ', [
                        'sql' => $query->toSql(),
                        'bindings' => $query->getBindings(),
                        'words' => $words
                    ]);

                    // 検索結果のデバッグ情報
                    $items = $query->latest()->get();
                    
                    // 各商品のマッチング状況を詳細にログ出力
                    foreach ($items as $item) {
                        $matches = [];
                        foreach ($words as $word) {
                            $matches[$word] = [
                                'full_match' => strpos($item->name, $word) !== false
                            ];
                        }
                        
                        \Log::info('商品のマッチング状況', [
                            'item_id' => $item->id,
                            'name' => $item->name,
                            'matches' => $matches
                        ]);
                    }

                    \Log::info('検索結果の概要', [
                        'total_count' => $items->count(),
                        'search_query' => $normalizedSearch,
                        'matched_words' => $words
                    ]);
                } else {
                    // 検索クエリがない場合のみ、自分が出品した商品を除外
                    if ($user) {
                        $query->where('user_id', '!=', $user->id);
                        \Log::info('通常表示時のクエリ', [
                            'sql' => $query->toSql(),
                            'bindings' => $query->getBindings()
                        ]);
                    }
                    $items = $query->latest()->get();
                }
                
                \Log::info('検索結果', [
                    'total_count' => $items->count(),
                    'search_query' => $search,
                    'items' => $items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'user_id' => $item->user_id,
                            'status' => $item->status
                        ];
                    })->toArray()
                ]);

                // 購入済み商品のステータスを一括更新
                DB::transaction(function () use ($items) {
                    foreach ($items as $item) {
                        if ($item->purchases()->exists() && $item->status !== 'sold') {
                            $item->status = 'sold';
                            $item->save();
                            \Log::info('商品ステータスを更新:', [
                                'item_id' => $item->id,
                                'name' => $item->name,
                                'status' => 'sold',
                                'seller_id' => $item->user_id,
                                'has_purchases' => $item->purchases()->exists()
                            ]);
                        }
                    }
                });

                // 取得された商品の詳細情報をログに出力
                foreach ($items as $item) {
                    $purchases = $item->purchases;
                    $purchaseUserIds = $purchases->pluck('user_id');
                    $isPurchasedByCurrentUser = $user ? $purchaseUserIds->contains($user->id) : false;
                    
                    \Log::info('商品情報:', [
                        'id' => $item->id,
                        'name' => $item->name,
                        'status' => $item->status,
                        'seller_id' => $item->user_id,
                        'seller_name' => ($item->user ? $item->user->name : '不明'),
                        'has_purchases' => $purchases->isNotEmpty(),
                        'purchase_count' => $purchases->count(),
                        'purchase_user_ids' => $purchaseUserIds,
                        'is_purchased_by_current_user' => $isPurchasedByCurrentUser,
                        'price' => $item->price,
                        'created_at' => $item->created_at
                    ]);
                }
            }

            return view('items', compact('items', 'view', 'search'));
        } catch (\Exception $e) {
            \Log::error('商品一覧取得エラー: ' . $e->getMessage());
            \Log::error('スタックトレース: ' . $e->getTraceAsString());
            return view('items', ['items' => collect(), 'view' => $view, 'search' => $search]);
        }
    }

    /**
     * 商品出品画面を表示
     */
    public function create()
    {
        return view('sell');
    }

    /**
     * 商品を出品
     */
    public function store(ExhibitionRequest $request)
    {
        \Log::info('商品出品リクエスト開始');
        \Log::info('リクエストデータ: ' . json_encode($request->all()));

        // バリデーション済みデータを取得
        $validated = $request->validated();

        // セッションから画像パスを取得
        $imagePath = session('imagePath');
        if (!$imagePath) {
            \Log::error('画像パスがセッションに存在しません');
            return redirect()->back()->withErrors(['error' => '商品画像を選択してください。'])->withInput();
        }

        \Log::info('セッションから取得した画像パス: ' . $imagePath);

        try {
            \DB::beginTransaction();
            \Log::info('トランザクション開始');

            $itemData = [
                'name' => $validated['name'],
                'brand' => $validated['brand'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'condition' => $validated['condition'],
                'category' => json_encode($validated['category']),
                'user_id' => auth()->id(),
                'image_path' => $imagePath,
            ];

            \Log::info('保存するデータ: ' . json_encode($itemData));

            // 画像ファイルの存在確認
            if (!Storage::exists('public/' . $imagePath)) {
                \Log::error('画像ファイルが存在しません: ' . storage_path('app/public/' . $imagePath));
                throw new \Exception('画像ファイルが見つかりません。');
            }

            $item = Item::create($itemData);
            \Log::info('商品保存完了: ' . json_encode($item));

            \DB::commit();
            \Log::info('トランザクションコミット完了');

            // セッションから画像パスを削除
            session()->forget('imagePath');

            return redirect('/')
                ->with('success', '商品を出品しました。');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('商品出品エラー: ' . $e->getMessage());
            \Log::error('スタックトレース: ' . $e->getTraceAsString());
            return redirect()->back()->withErrors(['error' => '商品の出品に失敗しました。'])->withInput();
        }
    }

    /**
     * 商品画像をアップロード
     */
    public function uploadImage(ImageUploadRequest $request)
    {
        if ($request->hasFile('image')) {
            try {
                // 元のファイル名を取得
                $originalName = $request->file('image')->getClientOriginalName();
                \Log::info('画像アップロード開始:', ['original_name' => $originalName]);

                // ファイル名の重複を避けるためにタイムスタンプを付加
                $fileName = time() . '_' . $originalName;
                \Log::info('生成されたファイル名:', ['file_name' => $fileName]);

                // 画像を保存（public/img/itemsディレクトリに保存）
                $path = $request->file('image')->storeAs('public/img/items', $fileName);
                \Log::info('画像保存パス:', ['path' => $path]);
                
                if ($path) {
                    // パスから'public/'を除去して保存
                    $imagePath = str_replace('public/', '', $path);
                    \Log::info('保存された画像パス:', ['image_path' => $imagePath]);
                    
                    // セッションに保存
                    session(['imagePath' => $imagePath]);
                    \Log::info('セッションに保存された画像パス:', ['session_image_path' => session('imagePath')]);
                    
                    // デバッグ情報をログに出力
                    Log::info('画像アップロード成功:', [
                        'original_name' => $originalName,
                        'file_name' => $fileName,
                        'path' => $path,
                        'image_path' => $imagePath,
                        'full_path' => storage_path('app/' . $path),
                        'public_path' => public_path('storage/' . $imagePath),
                        'exists' => Storage::exists($path),
                        'file_size' => Storage::size($path),
                        'mime_type' => Storage::mimeType($path)
                    ]);
                    
                    return redirect()->back()->with('success', '画像をアップロードしました。');
                }
            } catch (\Exception $e) {
                Log::error('画像アップロードエラー: ' . $e->getMessage());
                Log::error('スタックトレース: ' . $e->getTraceAsString());
                return redirect()->back()->with('error', '画像のアップロードに失敗しました。');
            }
        }

        Log::error('画像ファイルが存在しません');
        return redirect()->back()->with('error', '画像のアップロードに失敗しました。');
    }

    /**
     * 商品画像を削除
     */
    public function removeImage(Request $request)
    {
        if (session('imagePath')) {
            $imagePath = session('imagePath');
            
            // 画像ファイルを削除
            if (Storage::exists('public/' . $imagePath)) {
                Storage::delete('public/' . $imagePath);
            }
            
            // セッションから画像パスを削除
            session()->forget('imagePath');
            
            \Log::info('画像削除完了: ' . $imagePath);
        }
        
        return redirect()->route('sell.form')->with('success', '画像を削除しました。');
    }

    /**
     * 商品詳細を表示
     */
    public function show($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();
        $isGuest = !Auth::check();
        $hasComment = $user ? $item->comments->where('user_id', $user->id)->count() > 0 : false;
        
        // 未ログインユーザー向けのメッセージを設定
        $guestMessage = $isGuest ? '商品の購入やコメント投稿にはログインが必要です。' : null;

        // カテゴリー日本語変換
        $categoryMap = [
            'fashion' => 'ファッション',
            'electronics' => '家電',
            'interior' => 'インテリア',
            'ladies' => 'レディース',
            'mens' => 'メンズ',
            "men's" => 'メンズ',
            'cosmetics' => 'コスメ',
            'books' => '本',
            'games' => 'ゲーム',
            'sports' => 'スポーツ',
            'kitchen' => 'キッチン',
            'handmade' => 'ハンドメイド',
            'accessories' => 'アクセサリー',
            'toys' => 'おもちゃ',
            'baby' => 'ベビー・キッズ',
        ];
        $categories = collect(json_decode($item->category))->map(function($cat) use ($categoryMap) {
            $key = strtolower(trim($cat));
            return $categoryMap[$key] ?? $cat;
        });

        return view('detail', compact('item', 'hasComment', 'guestMessage', 'isGuest', 'categories'));
    }

    /**
     * 商品にコメントを追加
     */
    public function addComment(CommentRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $item->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return redirect()->back();
    }

    /**
     * 商品検索
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        
        $query = Item::query();
        
        if ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }
        
        $items = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('items.index', [
            'items' => $items,
            'keyword' => $keyword
        ]);
    }
}