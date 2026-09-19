<?php

namespace Tests\Feature\Advanced;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    //----- 書籍所有者は書籍を更新できる権限を持っていること -----
    public function test_book_owner_can_update_book(): void
    {
        $bookOwner = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $book = Book::create([
            'user_id' => $bookOwner->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた小説。',
        ]);

        $this->assertTrue($bookOwner->can('update', $book));
    }

    // ----- 書籍編集画面（GET /books/{book}/edit）にアクセスし、View に正しい book と genres が渡されていること（assertViewHas / viewData で検証） -----
    public function test_book_edit_page_has_correct_book_and_genres(): void
    {
        $bookOwner = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($bookOwner);

        $book = Book::create([
            'user_id' => $bookOwner->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた小説。',
        ]);

        $genre = Genre::create([
            'name' => '小説',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->get('/books/' . $book->id . '/edit');
        $response->assertStatus(200);

        // Viewに渡されたデータを取り出す
        $viewBook = $response->viewData('book');
        $viewGenres = $response->viewData('genres');

        // 正しい書籍が渡されていること
        $this->assertTrue($viewBook->is($book));

        // 作成したジャンルがgenresに含まれていること
        $this->assertTrue($viewGenres->contains($genre));

    }
}
