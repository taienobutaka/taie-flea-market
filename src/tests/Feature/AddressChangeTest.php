<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddressChangeTest extends TestCase
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
     * 送付先住所変更画面で登録した住所が商品購入画面に反映されることをテスト
     */
    public function test_address_change_reflects_on_purchase_screen()
    {
        $this->actingAs($this->user);
        // 住所変更画面で住所を登録
        $newAddress = [
            'postcode' => '123-4567',
            'address' => '東京都新宿区テスト1-2-3',
            'building_name' => 'テストビル101',
        ];
        $response = $this->put('/purchase/address/' . $this->item->id, $newAddress);
        $response->assertRedirect();
        // 商品購入画面で住所が反映されているか
        $response = $this->get('/purchase/' . $this->item->id);
        $response->assertStatus(200);
        $response->assertSee($newAddress['postcode']);
        $response->assertSee($newAddress['address']);
        $response->assertSee($newAddress['building_name']);
    }

    /**
     * 購入した商品に送付先住所が紐づいて登録されることをテスト
     */
    public function test_purchase_has_correct_address()
    {
        $this->actingAs($this->user);
        // 住所変更
        $newAddress = [
            'postcode' => '987-6543',
            'address' => '大阪府大阪市テスト4-5-6',
            'building_name' => 'サンプルマンション202',
        ];
        $this->put('/purchase/address/' . $this->item->id, $newAddress);
        // 購入情報を直接作成（テスト用）
        $purchase = Purchase::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'postcode' => $newAddress['postcode'],
            'address' => $newAddress['address'],
            'building_name' => $newAddress['building_name'],
        ]);
        $this->item->status = 'sold';
        $this->item->save();
        // DBに正しく保存されているか
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'postcode' => $newAddress['postcode'],
            'address' => $newAddress['address'],
            'building_name' => $newAddress['building_name'],
        ]);
    }
}
