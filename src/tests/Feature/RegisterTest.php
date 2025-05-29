<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 名前が入力されていない場合のテスト
     */
    public function test_register_validation_name_required()
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $response->assertSessionHasErrors(['username' => 'お名前を入力してください']);
    }

    /**
     * メールアドレスが入力されていない場合のテスト
     */
    public function test_register_validation_email_required()
    {
        $response = $this->post('/register', [
            'username' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * パスワードが入力されていない場合のテスト
     */
    public function test_register_validation_password_required()
    {
        $response = $this->post('/register', [
            'username' => 'Test User',
            'email' => 'test@example.com',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('password');
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * パスワードが7文字以下の場合のテスト
     */
    public function test_register_validation_password_min_length()
    {
        $response = $this->post('/register', [
            'username' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertSessionHasErrors('password');
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /**
     * パスワードが確認用パスワードと一致しない場合のテスト
     */
    public function test_register_validation_password_confirmation()
    {
        $response = $this->post('/register', [
            'username' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors('password_confirmation');
        $response->assertSessionHasErrors(['password_confirmation' => 'パスワードと一致しません']);
    }

    /**
     * 全ての項目が正しく入力された場合のテスト
     */
    public function test_register_success()
    {
        $userData = [
            'username' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect('/verification');
        $this->assertDatabaseHas('users', [
            'username' => $userData['username'],
            'email' => $userData['email'],
        ]);
    }
} 