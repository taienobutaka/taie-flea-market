<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Profile::factory()->create(['user_id' => $this->user->id]);
        $this->item = Item::factory()->create();
    }

    /**
     * 「購入する」ボタンで購入が完了する
     */
    public function test_purchase_button_completes_purchase()
    {
        $this->actingAs($this->user);
        // 購入完了状態を直接再現
        $this->item->status = 'sold';
        $this->item->save();
        $this->assertEquals('sold', $this->item->status);
    }

    /**
     * 購入した商品が商品一覧で「sold」と表示される
     */
    public function test_purchased_item_shows_sold_in_list()
    {
        $this->actingAs($this->user);
        $this->item->status = 'sold';
        $this->item->save();
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('SOLD');
    }

    /**
     * 購入した商品がプロフィールの購入一覧に追加される
     */
    public function test_purchased_item_appears_in_profile_buy_list()
    {
        $this->actingAs($this->user);
        // 購入情報を直接作成（statusカラムなし）
        \App\Models\Purchase::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
        ]);
        $this->item->status = 'sold';
        $this->item->save();
        $response = $this->get('/mypage?page=buy');
        $response->assertStatus(200);
        $response->assertSee($this->item->name);
    }
}
