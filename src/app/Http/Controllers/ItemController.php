<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ItemController extends Controller
{
    /**
     * 商品一覧を表示
     */
    public function index()
    {
        $items = Item::latest()->get();
        return view('items', compact('items'));
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
    public function store(Request $request)
    {
        \Log::info('商品出品リクエスト開始');
        \Log::info('リクエストデータ: ' . json_encode($request->all()));
        \Log::info('ログインユーザーID: ' . auth()->id());

        // バリデーション
        $validated = $request->validate([
            'category' => 'required|string',
            'condition' => 'required|string',
            'brand' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:1',
        ]);

        \Log::info('バリデーション結果: ' . json_encode($validated));

        // セッションから画像パスを取得
        $imagePath = session('imagePath');
        if (!$imagePath) {
            return redirect()->back()->withErrors(['error' => '商品画像を選択してください。'])->withInput();
        }

        try {
            \DB::beginTransaction();
            \Log::info('トランザクション開始');

            $itemData = [
                'name' => $validated['name'],
                'brand' => $validated['brand'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'condition' => $validated['condition'],
                'category' => $validated['category'],
                'user_id' => auth()->id(),
                'image_path' => $imagePath,
            ];

            \Log::info('保存するデータ: ' . json_encode($itemData));

            // データベース接続を確認
            \Log::info('データベース接続確認: ' . \DB::connection()->getDatabaseName());

            // テーブルが存在するか確認
            \Log::info('テーブル存在確認: ' . \Schema::hasTable('items'));

            // カラム情報を確認
            \Log::info('カラム情報: ' . json_encode(\Schema::getColumnListing('items')));

            // SQLクエリをログに記録
            \DB::enableQueryLog();
            $item = Item::create($itemData);
            \Log::info('実行されたSQL: ' . json_encode(\DB::getQueryLog()));

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
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // 元のファイル名を取得
            $originalName = $request->file('image')->getClientOriginalName();

            // ファイル名の重複を避けるためにタイムスタンプを付加
            $fileName = time() . '_' . $originalName;

            // 画像を保存
            $path = $request->file('image')->storeAs('public/img/items', $fileName);
            
            if ($path) {
                $imagePath = str_replace('public/', '', $path);
                
                // セッションに保存
                session(['imagePath' => $imagePath]);
                
                \Log::info('画像アップロード完了: ' . $imagePath);
                \Log::info('保存先フルパス: ' . storage_path('app/public/' . $imagePath));
                
                return redirect()->back()->with('success', '画像をアップロードしました。');
            }
        }

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
        return view('detail', compact('item'));
    }

    /**
     * 商品にコメントを追加
     */
    public function addComment(Request $request, $item_id)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $item = Item::findOrFail($item_id);
        $item->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return redirect()->back()->with('success', 'コメントを投稿しました。');
    }
}