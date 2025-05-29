<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends TestCase
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
     * ログイン済みユーザーはコメントを送信できる
     */
    public function test_logged_in_user_can_post_comment()
    {
        $this->actingAs($this->user);
        $response = $this->post('/item/' . $this->item->id . '/comment', [
            'content' => 'テストコメント',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'item_id' => $this->item->id,
            'user_id' => $this->user->id,
            'content' => 'テストコメント',
        ]);
        // コメント数が1件
        $response = $this->get('/item/' . $this->item->id);
        $response->assertSee('コメント(1)');
    }

    /**
     * 未ログインユーザーはコメントを送信できない
     */
    public function test_guest_cannot_post_comment()
    {
        $response = $this->post('/item/' . $this->item->id . '/comment', [
            'content' => 'ゲストコメント',
        ]);
        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('comments', [
            'item_id' => $this->item->id,
            'content' => 'ゲストコメント',
        ]);
    }

    /**
     * コメントが未入力の場合バリデーションメッセージが表示される
     */
    public function test_comment_required_validation()
    {
        $this->actingAs($this->user);
        $response = $this->post('/item/' . $this->item->id . '/comment', [
            'content' => '',
        ]);
        $response->assertSessionHasErrors('content');
    }

    /**
     * コメントが255字以上の場合バリデーションメッセージが表示される
     */
    public function test_comment_max_length_validation()
    {
        $this->actingAs($this->user);
        $longComment = str_repeat('あ', 256);
        $response = $this->post('/item/' . $this->item->id . '/comment', [
            'content' => $longComment,
        ]);
        $response->assertSessionHasErrors('content');
    }
}
