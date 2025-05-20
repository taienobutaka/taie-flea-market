<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * 認可の設定
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * バリデーションルール
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * カスタムエラーメッセージ
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'ログイン情報が登録されていません。',
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'ログイン情報が登録されていません。',
            'login' => 'ログイン情報が登録されていません。',
        ];
    }

    /**
     * バリデーションエラーの属性名を日本語に変更
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'email' => 'メールアドレス',
            'password' => 'パスワード',
        ];
    }
}
