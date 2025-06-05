<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Http\Requests\ExhibitionRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\CommentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    /**
     * 商品一覧を表示
     */
    public function index(Request $request)
    {
        try {
            // viewパラメータが存在する場合はpageパラメータに変換
            if ($request->has('view')) {
                return redirect()->route('items.index', [
                    'page' => $request->input('view'),
                    'search' => $request->input('search')
                ]);
            }

            $page = $request->input('page', 'recommended');
            $search = $request->input('search');
            $user = Auth::user();
            $isGuest = !Auth::check();
            
            \Log::info('商品一覧表示開始', [
                'page' => $page,
                'search' => $search,
                'user_id' => $user ? $user->id : '未ログイン',
                'is_authenticated' => !$isGuest
            ]);

            // 検索クエリがある場合の共通処理
            if ($search) {
                // 検索クエリを正規化（全角スペースを半角に変換、連続するスペースを1つに）
                $normalizedSearch = preg_replace('/\s+/', ' ', mb_convert_kana($search, 's'));
                
                // 助詞や接続詞を除外するための配列
                $excludeWords = ['と', 'や', 'の', 'を', 'に', 'へ', 'で', 'が', 'は', 'も', 'から', 'まで', 'など'];
                
                // 検索クエリを単語に分割し、助詞や接続詞を除外
                $words = array_filter(explode(' ', $normalizedSearch), function($word) use ($excludeWords) {
                    return !in_array($word, $excludeWords) && mb_strlen($word) > 0;
                });

                // 検索用の配列を作成
                $searchTerms = [];
                
                // 元の検索クエリをそのまま追加
                $searchTerms[] = $normalizedSearch;
                
                // 各単語を個別に追加
                foreach ($words as $word) {
                    if (mb_strlen($word) >= 2) {
                        $searchTerms[] = $word;
                    }
                }
                
                // 重複を除去
                $searchTerms = array_unique($searchTerms);

                // 検索条件に一致する商品を取得
                $searchQuery = Item::where(function($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $q->orWhere('name', 'LIKE', '%' . $term . '%');
                    }
                });

                // ログインユーザーの場合のみ、自分が出品した商品を除外し、検索結果をお気に入りに追加
                if ($user && !$isGuest) {
                    $searchQuery->where('user_id', '!=', $user->id);
                    
                    // 検索結果の商品をお気に入りに追加
                    $searchItems = $searchQuery->get();
                    foreach ($searchItems as $item) {
                        if (!$item->isFavoritedBy($user)) {
                            $item->favorites()->create(['user_id' => $user->id]);
                            \Log::info('検索結果の商品をお気に入りに追加', [
                                'item_id' => $item->id,
                                'name' => $item->name,
                                'user_id' => $user->id
                            ]);
                        }
                    }
                }
            }
            
            if ($page === 'mylist') {
                if ($isGuest) {
                    return view('items', ['items' => collect(), 'page' => 'mylist', 'search' => $search]);
                }

                // マイリスト表示用のクエリ
                $query = Item::with(['purchases', 'favorites' => function($query) use ($user) {
                    $query->where('user_id', $user->id);
                }]);

                // 検索条件がある場合は適用（ログインユーザーの場合のみ）
                if ($search && !$isGuest) {
                    $normalizedSearch = preg_replace('/\s+/', ' ', mb_convert_kana($search, 's'));
                    $excludeWords = ['と', 'や', 'の', 'を', 'に', 'へ', 'で', 'が', 'は', 'も', 'から', 'まで', 'など'];
                    $words = array_filter(explode(' ', $normalizedSearch), function($word) use ($excludeWords) {
                        return !in_array($word, $excludeWords) && mb_strlen($word) > 0;
                    });
                    $searchTerms = array_unique(array_merge([$normalizedSearch], $words));

                    $query->where(function($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->orWhere('name', 'LIKE', '%' . $term . '%');
                        }
                    });
                }

                // 自分が出品した商品を除外
                $query->where('user_id', '!=', $user->id);

                // お気に入り登録されている商品のみを取得
                $query->whereHas('favorites', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });

                $items = $query->latest()->get();
            } else {
                // おすすめ表示用のクエリ
                $query = Item::with(['purchases', 'favorites' => function($query) use ($user) {
                    if ($user) {
                        $query->where('user_id', $user->id);
                    }
                }]);

                // 検索条件がある場合は適用
                if ($search) {
                    $normalizedSearch = preg_replace('/\s+/', ' ', mb_convert_kana($search, 's'));
                    $excludeWords = ['と', 'や', 'の', 'を', 'に', 'へ', 'で', 'が', 'は', 'も', 'から', 'まで', 'など'];
                    $words = array_filter(explode(' ', $normalizedSearch), function($word) use ($excludeWords) {
                        return !in_array($word, $excludeWords) && mb_strlen($word) > 0;
                    });
                    $searchTerms = array_unique(array_merge([$normalizedSearch], $words));

                    $query->where(function($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->orWhere('name', 'LIKE', '%' . $term . '%');
                        }
                    });
                }

                // ログインユーザーの場合のみ、自分が出品した商品を除外
                if ($user) {
                    $query->where('user_id', '!=', $user->id);
                }

                $items = $query->latest()->get();
            }

            // 購入済み商品のステータスを一括更新
            DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    if ($item->purchases()->exists() && $item->status !== 'sold') {
                        $item->status = 'sold';
                        $item->save();
                    }
                }
            });

            return view('items', compact('items', 'page', 'search'));
        } catch (\Exception $e) {
            \Log::error('商品一覧取得エラー: ' . $e->getMessage());
            \Log::error('スタックトレース: ' . $e->getTraceAsString());
            return view('items', ['items' => collect(), 'page' => $page, 'search' => $search]);
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
                'image_path' => $imagePath, // パスはそのまま保存
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

                // 画像を保存（storage/app/public/img/itemsディレクトリに保存）
                $path = $request->file('image')->storeAs('img/items', $fileName, 'public');
                \Log::info('画像保存パス:', ['path' => $path]);
                
                if ($path) {
                    // セッションに保存（パスはそのまま保存）
                    session(['imagePath' => $path]);
                    \Log::info('セッションに保存された画像パス:', ['session_image_path' => session('imagePath')]);
                    
                    // デバッグ情報をログに出力
                    Log::info('画像アップロード成功:', [
                        'original_name' => $originalName,
                        'file_name' => $fileName,
                        'path' => $path,
                        'full_path' => storage_path('app/public/' . $path),
                        'public_path' => public_path('storage/' . $path),
                        'exists' => Storage::exists('public/' . $path),
                        'file_size' => Storage::size('public/' . $path),
                        'mime_type' => Storage::mimeType('public/' . $path)
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
        $item = Item::with(['comments.user.profile'])->findOrFail($item_id);
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