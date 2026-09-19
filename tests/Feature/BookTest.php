<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookTest extends TestCase
{
    use RefreshDatabase;

    //画面アクセス
    //書籍一覧ページ（/books）が正常に表示されること（200レスポンス）。
    public function test_books_index_can_be_accessed(): void
    {
        $response = $this->get('/books');
        $response->assertStatus(200);
    }

    //画面アクセス
    //認証ユーザーのみが書籍登録フォーム（/books/create）を表示できること。
    public function test_authenticated_user_book_create_form_can_be_accessed(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/books/create');
        $response->assertStatus(200);
    }

    //画面アクセス
    //ゲストはログインにリダイレクトされること。
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/books/create');
        $response->assertRedirect('/login');
    }


    //書籍CRUD-書籍詳細
    //書籍詳細ページ（/books/{book}）が正常に表示され、書籍タイトルが画面に含まれること。
    public function test_book_show_can_be_displayed(): void
    {
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

        $response = $this->get('/books/' . $book->id);
        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である');
    }

    //書籍CRUD-書籍登録
    //認証ユーザーが書籍を登録でき、ジャンルがbook_genreテーブルに紐付けられること。
    public function test_authenticated_user_can_create_book_with_genres(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $genre = Genre::create([
            'name' => '小説'
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

        $response = $this->post('/books', $bookData);
        $response->assertRedirect('/books');

        $book = Book::where('isbn', '9784101010014', )
            ->first();

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    //書籍CRUD-書籍登録
    //バリデーションエラー時は適切にエラーが返されること。
    public function test_book_creation_validation_errors(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $bookData = [
            'user_id' => $user->id,
            'title' => '',
            'author' => '',
            'isbn' => '',
            'published_date' => '',
            'description' => '',
            'genres' => [],
        ];

        $response = $this->from('/books/create')->post('/books', $bookData);
        $response->assertRedirect('/books/create');

        $response->assertSessionHasErrors([
            'title',
            'author',
            'genres',
        ]);
    }

    //書籍CRUD-書籍編集
    //書籍所有者のみが編集でき、ジャンルの同期（sync）が正しく動作すること。
    public function test_book_owner_can_update_book_with_genres(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $genre = Genre::create([
            'name' => '小説'
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

        $updatedGenre = Genre::create([
            'name' => 'ビジネス'
        ]);

        $updatedBook = [
            'title' => 'コンテナ物語',
            'author' => 'マルク・レビンソン',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'genres' => [$updatedGenre->id],
        ];

        $response = $this->put('/books/' . $book->id, $updatedBook);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/books');

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $updatedGenre->id,
        ]);

    }

    //書籍CRUD-書籍編集
    //書籍所有者以外は本を編集できない。403 Forbiddenとなること。
    public function test_non_owner_cannot_update_book(): void
    {
        $bookOwner = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $user = User::create([
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
        ]);

        $genre = Genre::create([
            'name' => '小説',
        ]);

        $this->actingAs($user);

        $book = Book::create([
            'user_id' => $bookOwner->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $book->genres()->attach($genre->id);

        $updatedBook = [
            'title' => 'コンテナ物語',
            'author' => 'マルク・レビンソン',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this->put('/books/' . $book->id, $updatedBook);
        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '吾輩は猫である',
        ]);
    }

    //書籍CRUD-書籍削除
    //書籍所有者のみが削除でき、削除後に書籍一覧にリダイレクトされること。
    //DBからレコードが削除されること。
    public function test_owner_can_delete_book(): void
    {
        $bookOwner = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($bookOwner);

        $genre = Genre::create([
            'name' => '小説'
        ]);

        $book = Book::create([
            'user_id' => $bookOwner->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->delete('/books/' . $book->id);
        $response->assertRedirect('/books');

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }


    //書籍CRUD-書籍削除
    //書籍所有者以外は本を削除できない。403 Forbiddenとなること。
    public function test_non_owner_cannot_delete_book(): void
    {
        $bookOwner = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $user = User::create([
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $book = Book::create([
            'user_id' => $bookOwner->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $response = $this->delete('/books/' . $book->id);
        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
