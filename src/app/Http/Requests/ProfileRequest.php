<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'nullable|image|mimes:jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'image.image' => '画像ファイルを選択してください。',
            'image.mimes' => '画像はjpegまたはpng形式でアップロードしてください。',
            'image.max' => '画像サイズは2MB以下にしてください。',
        ];
    }

    public function attributes(): array
    {
        return [
            'image' => 'プロフィール画像',
        ];
    }
}
