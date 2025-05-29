<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * 全商品を取得できることをテスト
     */
    public function test_can_get_all_items()
    {
        // テスト用の商品を3つ作成
        $items = Item::factory()->count(3)->create();

        // 商品一覧ページにアクセス
        $response = $this->get('/');

        // ステータスコードが200であることを確認
        $response->assertStatus(200);

        // 作成した商品が全て表示されていることを確認
        foreach ($items as $item) {
            $response->assertSee($item->name);
        }
    }

    /**
     * 購入済み商品に「Sold」ラベルが表示されることをテスト
     */
    public function test_sold_items_show_sold_label()
    {
        // テスト用の商品を作成
        $item = Item::factory()->create();
        
        // 商品を購入済みにする
        Purchase::create([
            'item_id' => $item->id,
            'user_id' => User::factory()->create()->id,
            'status' => 'completed',
            'payment_method' => 'credit_card',
            'amount' => $item->price,
            'postcode' => '123-4567',
            'address' => 'テスト住所',
        ]);

        // 商品一覧ページにアクセス
        $response = $this->get('/');

        // ステータスコードが200であることを確認
        $response->assertStatus(200);

        // 「Sold」ラベルが表示されていることを確認（大文字小文字を区別しない）
        $response->assertSee('SOLD', false);
    }

    /**
     * 自分が出品した商品が一覧に表示されないことをテスト
     */
    public function test_own_items_not_displayed_in_list()
    {
        // テストユーザーを作成
        $user = User::factory()->create();
        
        // ユーザーが出品した商品を作成
        $ownItem = Item::factory()->create([
            'user_id' => $user->id
        ]);
        
        // 他のユーザーが出品した商品を作成
        $otherItem = Item::factory()->create();

        // ユーザーとしてログイン
        $this->actingAs($user);

        // 商品一覧ページにアクセス
        $response = $this->get('/');

        // ステータスコードが200であることを確認
        $response->assertStatus(200);

        // 自分が出品した商品が表示されていないことを確認
        $response->assertDontSee($ownItem->name);
        
        // 他のユーザーが出品した商品は表示されていることを確認
        $response->assertSee($otherItem->name);
    }

    /**
     * 商品出品（登録）が正常に行われることをテスト
     */
    public function test_item_exhibition_registers_item_correctly()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // ダミー画像をstorageに配置（テスト用）
        $dummyImagePath = 'img/items/test_dummy.jpg';
        \Storage::disk('public')->put($dummyImagePath, 'dummy');
        // セッションに画像パスをセット
        session(['imagePath' => $dummyImagePath]);

        $postData = [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'テスト説明',
            'price' => 1234,
            'condition' => '良好',
            'category' => ['fashion', 'mens'],
        ];

        $response = $this->post('/sell', $postData);

        // 商品一覧にリダイレクトされる
        $response->assertRedirect('/');

        // DBに保存されていること
        $this->assertDatabaseHas('items', [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'テスト説明',
            'price' => 1234,
            'condition' => '良好',
            'user_id' => $user->id,
            'image_path' => $dummyImagePath,
        ]);
        // カテゴリはjson配列で保存
        $item = \App\Models\Item::where('name', 'テスト商品')->first();
        $this->assertNotNull($item);
        // カテゴリはjson文字列の場合も考慮
        $category = is_array($item->category) ? $item->category : json_decode($item->category, true);
        $this->assertEqualsCanonicalizing(['fashion', 'mens'], $category);
    }

    /**
     * 商品出品バリデーション（必須項目未入力）
     */
    public function test_item_exhibition_validation_required_fields()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        // 画像パス未セット
        session()->forget('imagePath');
        $response = $this->post('/sell', []);
        $response->assertSessionHasErrors(['name', 'description', 'category', 'condition', 'price', 'image']);
    }
}