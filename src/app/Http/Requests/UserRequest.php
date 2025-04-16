<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * リクエストの認可を設定
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // 認可を許可
    }

    /**
     * バリデーションルールを定義
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    /**
     * バリデーションエラーメッセージを定義
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'username.required' => 'お名前を入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メール形式が正しくありません',
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードと一致しません',
        ];
    }
}
