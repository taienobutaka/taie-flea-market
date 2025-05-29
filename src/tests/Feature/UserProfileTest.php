<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $profile;
    private $item1;
    private $item2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->profile = Profile::factory()->create([
            'user_id' => $this->user->id,
            'image_path' => 'profiles/test.jpg',
            'postcode' => '111-2222',
            'address' => '東京都渋谷区1-2-3',
        ]);
        $this->item1 = Item::factory()->create(['user_id' => $this->user->id, 'name' => '出品商品A']);
        $this->item2 = Item::factory()->create(['user_id' => $this->user->id, 'name' => '出品商品B']);
        // 購入商品
        $otherItem = Item::factory()->create(['name' => '購入商品C']);
        Purchase::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $otherItem->id,
        ]);
    }

    /**
     * プロフィールページで必要な情報が表示されることをテスト
     */
    public function test_profile_page_shows_all_user_info()
    {
        $this->actingAs($this->user);
        $response = $this->get('/mypage');
        $response->assertStatus(200);
        // プロフィール画像
        $response->assertSee($this->profile->image_path);
        // ユーザー名
        $response->assertSee($this->profile->username);
        // 出品した商品一覧
        $response->assertSee('出品商品A');
        $response->assertSee('出品商品B');
        // 購入した商品一覧
        $response = $this->get('/mypage?page=buy');
        $response->assertStatus(200);
        $response->assertSee('購入商品C');
    }

    /**
     * プロフィール編集ページで初期値が正しく表示されることをテスト
     */
    public function test_profile_edit_page_shows_initial_values()
    {
        $this->actingAs($this->user);
        $response = $this->get('/mypage/profile');
        $response->assertStatus(200);
        $response->assertSee($this->profile->image_path);
        $response->assertSee($this->profile->username);
        $response->assertSee($this->profile->postcode);
        $response->assertSee($this->profile->address);
    }
}
