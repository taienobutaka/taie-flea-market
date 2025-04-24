<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'payment_method' => 'required|in:convenience_store,credit_card',
            'shipping_address' => 'required|string|max:255',
            'postal_code' => 'required|string|regex:/^\d{3}-\d{4}$/',
        ];
    }

    public function messages()
    {
        return [
            'payment_method.required' => '支払い方法を選択してください。',
            'payment_method.in' => '有効な支払い方法を選択してください。',
            'shipping_address.required' => '配送先住所を入力してください。',
            'shipping_address.max' => '配送先住所は255文字以内で入力してください。',
            'postal_code.required' => '郵便番号を入力してください。',
            'postal_code.regex' => '郵便番号はXXX-XXXXの形式で入力してください。',
        ];
    }
} 