<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

class ExhibitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'category' => ['required', 'array'],
            'category.*' => ['required', 'string'],
            'condition' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'brand' => ['nullable', 'string', 'max:255'],
        ];

        // セッションから画像パスを取得
        $imagePath = session('imagePath');
        
        // 画像パスが存在しない場合は、必須ルールを追加
        if (!$imagePath) {
            $rules['image'] = ['required'];
        } else {
            $rules['image'] = ['nullable', 'image'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '商品名は必須項目です。',
            'name.string' => '商品名は文字列で入力してください。',
            'name.max' => '商品名は255文字以内で入力してください。',
            'description.required' => '商品の説明は必須項目です。',
            'description.string' => '商品の説明は文字列で入力してください。',
            'description.max' => '商品の説明は255文字以内で入力してください。',
            'category.required' => 'カテゴリーは必須項目です。',
            'category.array' => 'カテゴリーは配列で入力してください。',
            'category.*.required' => 'カテゴリーは必須項目です。',
            'category.*.string' => 'カテゴリーは文字列で入力してください。',
            'condition.required' => '商品の状態は必須項目です。',
            'condition.string' => '商品の状態は文字列で入力してください。',
            'price.required' => '価格は必須項目です。',
            'price.numeric' => '価格は数値で入力してください。',
            'price.min' => '価格は0円以上で入力してください。',
            'brand.max' => 'ブランド名は255文字以内で入力してください。',
            'image.required' => '商品画像は必須項目です。',
            'image.image' => '商品画像は有効な画像ファイルである必要があります。',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => '商品名',
            'description' => '商品の説明',
            'price' => '価格',
            'category' => 'カテゴリー',
            'condition' => '商品の状態',
            'brand' => 'ブランド名',
            'image' => '商品画像',
        ];
    }

    /**
     * バリデーション前の処理
     */
    protected function prepareForValidation()
    {
        // セッションから画像パスを取得
        $imagePath = session('imagePath');
        
        // 画像パスが存在しない場合は、バリデーションエラーを発生させる
        if (!$imagePath) {
            $this->merge(['image' => null]);
            $this->rules()['image'] = ['required'];
        }
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw (new \Illuminate\Validation\ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
} 