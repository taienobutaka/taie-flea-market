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
            'payment_method' => 'required|in:convenience,credit_card',
            'shipping_address' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'payment_method.required' => '支払い方法を選択してください。',
            'payment_method.in' => '有効な支払い方法を選択してください。',
            'shipping_address.required' => '配送先を選択してください。',
        ];
    }

    public function attributes()
    {
        return [
            'payment_method' => '支払い方法',
            'shipping_address' => '配送先',
        ];
    }
} 