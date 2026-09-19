<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => 'required|integer|exists:books,id',
            'target_date' => 'required|date|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を一つ選択してください。',
            'target_date.required' => '日付を選択してください。',
            'target_date.after_or_equal' => '今日以降の日付を選択してください。',
        ];
    }
}
