<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    //公開API GET（一覧/詳細）
    //GET /api/v1/books が正しいJSON（data + meta）を返すこと。
    public function test_books_index_returns_correct_json_structure(): void
    {
        $genre = Genre::create([
            'name' => '小説',
        ]);

        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $book1 = Book::create([
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);
        $book2 = Book::create([
            'user_id' => $user->id,
            'title' => '人を動かす',
            'author' => 'D・カーネギー',
            'isbn' => '9784422100524',
            'published_date' => '1936-10-01',
            'description' => '良好な人間関係を築き、人の心を動かすための原則を紹介する自己啓発書。',
        ]);

        $book1->genres()->attach($genre->id);
        $book2->genres()->attach($genre->id);

        $response = $this->getJson('/api/v1/books');

        $response->assertOk();

        $response->assertJsonCount(2, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'genres',
                    'average_rating',
                    'review_count',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
    }

    //公開API GET（一覧/詳細）
    // GET /api/v1/books/{book} が正しいJSON（data + meta）を返すこと。
    public function test_book_show_returns_correct_json_structure(): void
    {
        $genre = Genre::create([
            'name' => '小説',
        ]);

        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->getJson('/api/v1/books/' . $book->id);

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'genres',
                'reviews',
            ],
        ]);
    }

    // 公開API GET（一覧/詳細）
    // 存在しないIDで404とエラーJSONを返すこと。
    public function test_not_found_returns_404(): void
    {
        $response = $this->getJson('/api/v1/books/99');

        $response->assertStatus(404);

        $response->assertExactJson([
            'message' => '書籍が見つかりませんでした。',
        ]);
    }

    //公開API POST（作成）
    //POST /api/v1/books で正常データを送信したとき201が返り、booksテーブルとbook_genreテーブルにレコードが作成されること。

    public function test_books_create_returns_201(): void
    {
        $genre = Genre::create([
            'name' => '小説',
        ]);

        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $bookData = [
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
            'genres' => [$genre->id],
        ];

        $response = $this->postJson('/api/v1/books', $bookData);
        $book = Book::first();

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '吾輩は猫である',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    //公開API POST（作成）
    //POST /api/v1/books でバリデーションエラー時に422とエラーメッセージが返ること。

    public function test_books_create_returns_422(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $bookData = [
            'user_id' => $user->id,
            'title' => '',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
            'genres' => [],
        ];

        $response = $this->postJson('/api/v1/books', $bookData);

        $response->assertStatus(422);

        $response->assertJson([
            'errors' => [
                'title' => [
                    'タイトルを入力してください。',
                ],
                'genres' => [
                    'ジャンルを1つ以上選択してください。',
                ],
            ],
        ]);
    }

    //公開API PUT（更新）
    //PUT /api/v1/books/{book} で正しいデータを送信したとき200が返り、更新内容がDBに反映されること。

    public function test_books_update_returns_200(): void
    {
        $genre = Genre::create([
            'name' => '小説',
        ]);

        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $book->genres()->attach($genre->id);

        $updateBookData = [
            'user_id' => $user->id,
            'title' => '人を動かす',
            'author' => 'D・カーネギー',
            'isbn' => '9784422100524',
            'published_date' => '1936-10-01',
            'description' => '良好な人間関係を築き、人の心を動かすための原則を紹介する自己啓発書。',
            'genres' => [$genre->id],
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $updateBookData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $user->id,
            'title' => '人を動かす',
            'isbn' => '9784422100524',
        ]);
    }

    // 公開API PUT（更新）
    // 存在しないIDで404が返ること。
    public function test_books_update_not_found_returns_404(): void
    {
        $response = $this->putJson('/api/v1/books/99');

        $response->assertStatus(404);
    }


    //公開API DELETE（削除）
    //DELETE /api/v1/books/{book} で書籍を削除したとき204が返り、booksテーブルから該当レコードが削除されること。

    public function test_books_delete_returns_204(): void
    {
        $genre = Genre::create([
            'name' => '小説',
        ]);

        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->deleteJson('/api/v1/books/' . $book->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
            'user_id' => $user->id,
        ]);
    }

    // 公開API DELETE（削除）
    // 存在しないIDで404が返ること。
    public function test_books_delete_not_found_returns_404(): void
    {
        $response = $this->deleteJson('/api/v1/books/99');

        $response->assertStatus(404);
    }
}
