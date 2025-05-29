<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが入力されていない場合のテスト
     */
    public function test_login_validation_email_required()
    {
        $response = $this->post('/login', [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * パスワードが入力されていない場合のテスト
     */
    public function test_login_validation_password_required()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('password');
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * 入力情報が間違っている場合のテスト
     */
    public function test_login_validation_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('login');
        $response->assertSessionHasErrors(['login' => 'メールアドレスまたはパスワードが間違っています']);
    }

    /**
     * 正しい情報が入力された場合のテスト
     */
    public function test_login_success()
    {
        // テスト用ユーザーを作成
        $user = User::create([
            'username' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // メール認証未完了の場合は認証画面へリダイレクト
        $response->assertRedirect('/verification');
        $this->assertAuthenticated();
    }

    /**
     * ログアウト機能のテスト
     */
    public function test_logout()
    {
        // テスト用ユーザーを作成してログイン
        $user = User::create([
            'username' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
} 