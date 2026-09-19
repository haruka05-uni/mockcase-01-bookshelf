<?php

namespace Tests\Feature\Advanced;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class IsbnSearchTest extends TestCase
{

    use RefreshDatabase;

    // ----- Http::fake()で外部APIをモック化し、正常に書籍情報が返ること。 -----
    public function test_isbn_search_returns_book_information(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => '吾輩は猫である',
                            'authors' => ['夏目漱石'],
                            'publishedDate' => '1905-01-01',
                            'description' => '猫の視点から人間社会を描いた小説。',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/cat-book.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $isbn = '9784101010014';

        $response = $this->get('/books/isbn/' . $isbn);

        $response->assertStatus(200);

        $response->assertJson([
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を描いた小説。',
            'image_url' => 'https://example.com/cat-book.jpg',
        ]);
    }

    // ----- 13桁以外のISBNで400エラーが返ること -----
    public function test_isbn_search_returns_400_for_invalid_isbn(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $isbn = '00000000';

        $response = $this->get('/books/isbn/' . $isbn);

        $response->assertStatus(400);
    }

    // ----- APIが空の結果を返した場合に404エラーが返ること -----
    public function test_isbn_search_returns_404_when_book_is_not_found(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [],
            ], 200),
        ]);

        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $isbn = '9784101010014';

        $response = $this->get('/books/isbn/' . $isbn);

        $response->assertStatus(404);

        $response->assertJson([
            'error' => '書籍が見つかりませんでした。',
        ]);
    }

    // ----- API通信例外時に500エラーが返ること -----
    public function test_isbn_search_returns_500_when_api_request_fails(): void
    {
        Http::fake(function () {
            throw new \Exception('API通信に失敗しました。');
        });

        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $isbn = '9784101010014';

        $response = $this->get('/books/isbn/' . $isbn);

        $response->assertStatus(500);

        $response->assertJson([
            'error' => 'API通信エラーが発生しました。',
        ]);
    }
}
