<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteTest extends TestCase
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
     * いいねアイコン押下で商品がいいね登録され、合計値が増加することをテスト
     */
    public function test_favorite_icon_adds_favorite_and_increases_count()
    {
        $this->actingAs($this->user);
        $response = $this->post('/favorites/' . $this->item->id, [
            'page' => 'recommended',
        ]);
        $response->assertRedirect();
        $response = $this->get('/item/' . $this->item->id);
        $response->assertStatus(200);
        $response->assertSee('1'); // いいね数
    }

    /**
     * いいね済みアイコンは色が変化することをテスト
     */
    public function test_favorite_icon_color_changes_when_favorited()
    {
        $this->actingAs($this->user);
        $this->post('/favorites/' . $this->item->id, ['page' => 'recommended']);
        $response = $this->get('/item/' . $this->item->id);
        $response->assertStatus(200);
        $response->assertSee('favorite-button--active');
    }

    /**
     * いいねアイコン再押下でいいね解除・合計値減少をテスト
     */
    public function test_favorite_icon_removes_favorite_and_decreases_count()
    {
        $this->actingAs($this->user);
        $this->post('/favorites/' . $this->item->id, ['page' => 'recommended']);
        $this->post('/favorites/' . $this->item->id, ['page' => 'recommended']);
        $response = $this->get('/item/' . $this->item->id);
        $response->assertStatus(200);
        $response->assertSee('0'); // いいね数
    }
}
