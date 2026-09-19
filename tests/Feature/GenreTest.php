<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    // ジャンル一覧
    // ジャンル一覧ページが正常に表示されること。
    public function test_genres_index_can_be_accessed(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/genres');
        $response->assertStatus(200);
    }

    // ジャンル登録フォーム
    // 認証ユーザーがジャンル登録フォーム（/genres/create）を表示できること。
    public function test_authenticated_user_review_create_form_can_be_accessed(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/genres/create');
        $response->assertStatus(200);
    }

    // ジャンル登録
    // 認証ユーザーがジャンルを作成でき、genresテーブルにレコードが作成されること。
    public function test_authenticated_user_can_create_genre(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $genreData = [
            'name' => '小説',
        ];

        $response = $this->post('/genres/', $genreData);

        $this->assertDatabaseHas('genres', [
            'name' => '小説',
        ]);
    }

    // ジャンル登録
    // バリデーションエラー時は適切にエラーが返されること。
    public function test_genre_creation_validation_errors(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $genreData = [
            'name' => '',
        ];

        $response = $this->from('/genres/create')->post('/genres', $genreData);
        $response->assertRedirect('/genres/create');

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    // ジャンル詳細
    // ジャンル詳細ページ（/genres/{genre}）でジャンルに紐づく書籍タイトルが表示されること。
    public function test_genre_show_displays_related_book_title(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $genre = Genre::create([
            'name' => '小説',
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

        $this->actingAs($user);

        $response = $this->get('/genres/'.$genre->id);
        $response->assertSee('吾輩は猫である');
    }

    // ジャンル編集
    // 認証ユーザーがジャンル名を更新でき、genresテーブルのレコードが更新されること。
    public function test_authenticated_user_can_update_genre(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $genre = Genre::create([
            'name' => '小説',
        ]);

        $this->actingAs($user);

        $response = $this->get('/genres/'.$genre->id.'/edit');
        $response->assertStatus(200);

        $updatedGenre = [
            'name' => 'ビジネス',
        ];

        $response = $this->put('/genres/'.$genre->id, $updatedGenre);
        $response->assertRedirect('/genres');

        $this->assertDatabaseMissing('genres', [
            'name' => '小説',
        ]);

        $this->assertDatabaseHas('genres', [
            'name' => 'ビジネス',
        ]);

    }

    // ジャンル削除制約
    // 書籍が紐付いているジャンルは削除できず、エラーメッセージが表示されること。
    public function test_genre_with_books_cannot_be_deleted(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $genre = Genre::create([
            'name' => '小説',
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

        $this->actingAs($user);

        $response = $this->delete('/genres/'.$genre->id);
        $response->assertSessionHas(
            'error',
            'このジャンルには書籍が紐付いているため削除できません。'
        );

        $this->assertDatabaseHas('genres', [
            'name' => '小説',
        ]);
    }

    // ジャンル削除制約
    // 紐付きがないジャンルは正常に削除できること。
    public function test_genre_without_books_can_be_deleted(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $genre = Genre::create([
            'name' => '小説',
        ]);

        $this->actingAs($user);

        $response = $this->delete('/genres/'.$genre->id);

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
            'name' => '小説',
        ]);
    }
}
