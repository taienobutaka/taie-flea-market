<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentMethodTest extends TestCase
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
     * 支払い方法選択画面で選択が即時反映されることをテスト
     */
    public function test_payment_method_selection_is_reflected()
    {
        $this->actingAs($this->user);
        // 支払い方法選択画面を開く
        $response = $this->get('/purchase/' . $this->item->id);
        $response->assertStatus(200);
        // プルダウンで支払い方法を選択（POSTでセッションに保存される想定）
        $postResponse = $this->post('/purchase/' . $this->item->id . '/payment-method', [
            'payment_method' => 'convenience',
        ]);
        $postResponse->assertRedirect();
        // 再度画面を開き、選択が反映されているか確認
        $response = $this->get('/purchase/' . $this->item->id);
        $response->assertStatus(200);
        $response->assertSee('convenience');
    }
}
