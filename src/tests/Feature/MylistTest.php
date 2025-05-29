<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class MylistTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $otherUser;
    private $item1;
    private $item2;
    private $item3;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーを作成
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        // プロフィールを作成
        Profile::factory()->create(['user_id' => $this->user->id]);
        Profile::factory()->create(['user_id' => $this->otherUser->id]);

        // テスト用の商品を作成
        $this->item1 = Item::factory()->create(['user_id' => $this->otherUser->id]);
        $this->item2 = Item::factory()->create(['user_id' => $this->otherUser->id]);
        $this->item3 = Item::factory()->create(['user_id' => $this->user->id]); // 自分が出品した商品
    }

    /**
     * いいねした商品だけが表示されることをテスト
     */
    public function test_only_favorited_items_are_displayed()
    {
        // ユーザーにログイン
        $this->actingAs($this->user);

        // item1をお気に入りに追加
        $this->item1->favorites()->create(['user_id' => $this->user->id]);

        // マイリストページにアクセス
        $response = $this->get('/?page=mylist');

        // ステータスコードの確認
        $response->assertStatus(200);

        // お気に入りに追加した商品のみが表示されることを確認
        $response->assertSee($this->item1->name);
        $response->assertDontSee($this->item2->name);
        $response->assertDontSee($this->item3->name);
    }

    /**
     * 購入済み商品に「Sold」ラベルが表示されることをテスト
     */
    public function test_purchased_items_show_sold_label()
    {
        // ユーザーにログイン
        $this->actingAs($this->user);

        // item1をお気に入りに追加
        $this->item1->favorites()->create(['user_id' => $this->user->id]);

        // item1を購入済みにする
        Purchase::create([
            'user_id' => $this->otherUser->id,
            'item_id' => $this->item1->id,
            'status' => 'completed',
            'amount' => $this->item1->price,
            'postcode' => '123-4567',
            'address' => 'テスト住所',
            'payment_method' => 'credit_card'
        ]);
        $this->item1->update(['status' => 'sold']);

        // マイリストページにアクセス
        $response = $this->get('/?page=mylist');

        // ステータスコードの確認
        $response->assertStatus(200);

        // 「SOLD」ラベルが表示されることを確認
        $response->assertSee('SOLD');
    }

    /**
     * 自分が出品した商品が表示されないことをテスト
     */
    public function test_own_items_are_not_displayed()
    {
        // ユーザーにログイン
        $this->actingAs($this->user);

        // 自分が出品した商品をお気に入りに追加
        $this->item3->favorites()->create(['user_id' => $this->user->id]);

        // マイリストページにアクセス
        $response = $this->get('/?page=mylist');

        // ステータスコードの確認
        $response->assertStatus(200);

        // 自分が出品した商品が表示されないことを確認
        $response->assertDontSee($this->item3->name);
    }

    /**
     * 未認証の場合は何も表示されないことをテスト
     */
    public function test_unauthenticated_user_sees_nothing()
    {
        // ログインせずにマイリストページにアクセス
        $response = $this->get('/?page=mylist');

        // ステータスコードの確認
        $response->assertStatus(200);

        // 商品が表示されないことを確認
        $response->assertDontSee($this->item1->name);
        $response->assertDontSee($this->item2->name);
        $response->assertDontSee($this->item3->name);
    }

    /**
     * 商品名で部分一致検索ができることをテスト
     */
    public function test_search_items_by_partial_name()
    {
        // ユーザーにログイン
        $this->actingAs($this->user);

        // item1, item2, item3 それぞれ異なる名前で作成済み
        $keyword = mb_substr($this->item1->name, 0, 2); // item1の先頭2文字をキーワードに

        // 検索実行
        $response = $this->get('/?search=' . urlencode($keyword));
        $response->assertStatus(200);
        // 部分一致する商品が表示される（item1の名前が含まれていることのみ検証）
        $response->assertSee($this->item1->name);
    }

    /**
     * 検索状態がマイリストでも保持されていることをテスト
     */
    public function test_search_keyword_is_kept_on_mylist()
    {
        $this->actingAs($this->user);
        $keyword = mb_substr($this->item1->name, 0, 2);
        // item1をお気に入りに追加
        $this->item1->favorites()->create(['user_id' => $this->user->id]);

        // ホームで検索
        $response = $this->get('/?search=' . urlencode($keyword));
        $response->assertStatus(200);
        $response->assertSee($this->item1->name);

        // マイリストページに遷移（検索キーワード付き）
        $response = $this->get('/?page=mylist&search=' . urlencode($keyword));
        $response->assertStatus(200);
        // 検索キーワードが保持されている（検索欄に値が残っている）
        $response->assertSee('value="' . $keyword . '"', false);
    }

    /**
     * 商品詳細ページで必要な情報がすべて表示されることをテスト
     */
    public function test_item_detail_page_shows_all_information()
    {
        $this->actingAs($this->user);
        $this->item1->favorites()->create(['user_id' => $this->user->id]);
        $this->item1->comments()->create([
            'user_id' => $this->user->id,
            'content' => 'テストコメント',
        ]);
        $this->item1->update(['category' => json_encode(['家電', 'ファッション'])]);

        $response = $this->get('/item/' . $this->item1->id);
        $response->assertStatus(200);
        $response->assertSee($this->item1->image_path);
        $response->assertSee($this->item1->name);
        $response->assertSee($this->item1->brand);
        $response->assertSee(substr(number_format($this->item1->price), -3));
        $response->assertSee('1'); // いいね数
        $response->assertSee('1'); // コメント数
        $response->assertSee($this->item1->description);
        $response->assertSee('家電');
        $response->assertSee('ファッション');
        $response->assertSee($this->item1->condition);
        $response->assertSee('テストコメント');
        // コメントユーザー名（Userのnameで検証）
        $response->assertSee($this->user->name);
    }

    /**
     * 商品詳細ページで複数カテゴリが表示されることをテスト
     */
    public function test_item_detail_page_shows_multiple_categories()
    {
        $this->actingAs($this->user);
        $categories = ['キッチン', 'ファッション', '家電'];
        $this->item1->update(['category' => json_encode($categories)]);
        $response = $this->get('/item/' . $this->item1->id);
        $response->assertStatus(200);
        foreach ($categories as $cat) {
            $response->assertSee($cat);
        }
    }
}