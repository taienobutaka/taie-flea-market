<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // ルート名を取得
        $routeName = $this->route()->getName();

        // 住所変更画面の場合
        if ($routeName === 'purchase.address.update') {
            return [
                'postcode' => 'required|string|size:8|regex:/^\d{3}-\d{4}$/',
                'address' => 'required|string|max:255',
                'building_name' => 'required|string|max:255',
            ];
        }

        // プロフィール設定画面の場合
        return [
            'username' => 'required|string|max:255',
            'postcode' => 'required|string|size:8|regex:/^\d{3}-\d{4}$/',
            'address' => 'required|string|max:255',
            'building_name' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        // ルート名を取得
        $routeName = $this->route()->getName();

        // 住所変更画面の場合
        if ($routeName === 'purchase.address.update') {
            return [
                'postcode.required' => '郵便番号は必須項目です。',
                'postcode.size' => '郵便番号は8文字で入力してください。',
                'postcode.regex' => '郵便番号はXXX-XXXXの形式で入力してください。',
                'address.required' => '住所は必須項目です。',
                'building_name.required' => '建物名は必須項目です。',
            ];
        }

        // プロフィール設定画面の場合
        return [
            'username.required' => 'ユーザー名は必須項目です。',
            'username.max' => 'ユーザー名は255文字以内で入力してください。',
            'postcode.required' => '郵便番号は必須項目です。',
            'postcode.size' => '郵便番号は8文字で入力してください。',
            'postcode.regex' => '郵便番号はXXX-XXXXの形式で入力してください。',
            'address.required' => '住所は必須項目です。',
            'building_name.required' => '建物名は必須項目です。',
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'ユーザー名',
            'postcode' => '郵便番号',
            'address' => '住所',
            'building_name' => '建物名',
        ];
    }
} 