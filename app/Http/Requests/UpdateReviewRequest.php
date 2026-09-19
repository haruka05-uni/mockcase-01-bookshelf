<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'comment.max' => 'コメントは255文字以内で入力してください。',
        ];
    }
}
