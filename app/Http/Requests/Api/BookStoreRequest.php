<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;


class BookStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|digits:13|unique:books,isbn',
            'published_date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'genres' => 'required|array|min:1',
            'genres.*' => 'integer|exists:genres,id',
            'image_url' => 'nullable|url|max:255',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'author.required' => '著者名を入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',
            'isbn.required' => 'ISBNを入力してください。',
            'isbn.digits' => 'ISBNは13桁で入力してください。',
            'isbn.unique' => 'このISBNは既に登録されています。',
            'published_date.required' => '出版日を入力してください。',
            'published_date.date' => '正しい出版日を入力してください。',
            'genres.required' => 'ジャンルを1つ以上選択してください。',
            'image_url.url' => '画像URLはURL形式で入力してください。',
        ];
    }
}
