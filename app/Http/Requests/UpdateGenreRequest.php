<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGenreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:20',
                Rule::unique('genres', 'name')->ignore($this->route('genre')),
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'ジャンル名を入力してください。',
            'name.unique' => 'このジャンル名は既に登録されています。',
            'name.max' => 'このジャンル名は既に登録されています。',
        ];
    }
}
