<?php

namespace Tests\Feature\Advanced;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Book;
use App\Models\Genre;
use Laravel\Sanctum\Sanctum;

class SanctumAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    //----- POST 未認証時に 401 Unauthorizedが返ること。 -----
    public function test_unauthenticated_user_cannot_create_book_via_api(): void
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

        $response->assertStatus(401);
    }

    //----- PUT 未認証時に 401 Unauthorizedが返ること。 -----
    public function test_unauthenticated_user_cannot_update_book_via_api(): void
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

        $response->assertStatus(401);

    }

    //----- DELETE 未認証時に 401 Unauthorizedが返ること。 -----
    public function test_unauthenticated_user_cannot_delete_book_via_api(): void
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

        $response->assertStatus(401);
    }

    //----- 他ユーザーが書籍の更新を試みた場合に BookPolicy により 403 Forbidden が返ること。 -----
    public function test_non_owner_cannot_update_book_via_api(): void
    {
        $genre = Genre::create([
            'name' => '小説',
        ]);

        $bookOwner = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $anotherUser = User::create([
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
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

        $updateBookData = [
            'user_id' => $bookOwner->id,
            'title' => '人を動かす',
            'author' => 'D・カーネギー',
            'isbn' => '9784422100524',
            'published_date' => '1936-10-01',
            'description' => '良好な人間関係を築き、人の心を動かすための原則を紹介する自己啓発書。',
            'genres' => [$genre->id],
        ];

        Sanctum::actingAs($anotherUser);

        $response = $this->putJson('/api/v1/books/' . $book->id, $updateBookData);

        $response->assertStatus(403);
    }

    //----- 他ユーザーが書籍の削除を試みた場合に BookPolicy により 403 Forbidden が返ること。 -----
    public function test_non_owner_cannot_delete_book_via_api(): void
    {
        $genre = Genre::create([
            'name' => '小説',
        ]);

        $bookOwner = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $anotherUser = User::create([
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
        ]);

        $book = Book::create([
            'user_id' => $bookOwner->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        Sanctum::actingAs($anotherUser);

        $book->genres()->attach($genre->id);

        $response = $this->deleteJson('/api/v1/books/' . $book->id);

        $response->assertStatus(403);
    }
}
