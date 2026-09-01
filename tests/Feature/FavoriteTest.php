<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Book;


class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    //お気に入り追加
    //認証ユーザーがお気に入りを追加でき、favoritesテーブルにレコードが作成されること。
    public function test_authenticated_user_can_add_book_to_favorites(): void
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

        $this->actingAs($user);

        $response = $this->post('/books/' . $book->id . '/favorites');

        $this->assertDatabaseHas('favorites', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
    }

    //お気に入り解除
    //認証ユーザーがお気に入りを追加でき、favoritesテーブルにレコードが作成されること。
    public function test_authenticated_user_can_remove_book_from_favorites(): void
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

        $this->actingAs($user);
        $book->favoritedByUsers()->attach($user->id);

        $this->post('/books/' . $book->id . '/favorites');

        $this->assertDatabaseMissing('favorites', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
    }

    //お気に入りトグル
    //お気に入りのトグル（追加→解除→追加）が正しく動作すること。
    public function test_authenticated_user_can_toggle_book_favorite(): void
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

        $this->actingAs($user);

        $this->post('/books/' . $book->id . '/favorites');

        $this->assertDatabaseHas('favorites', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $this->post('/books/' . $book->id . '/favorites');

        $this->assertDatabaseMissing('favorites', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $this->post('/books/' . $book->id . '/favorites');

        $this->assertDatabaseHas('favorites', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
    }

    //お気に入り一覧
    //お気に入り一覧ページが正常に表示されること。
    public function test_favorites_index_can_be_accessed(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/favorites');
        $response->assertStatus(200);
    }

    //ゲスト制限
    //ゲストがお気に入り操作を行うとログインにリダイレクトされること。
    public function test_guest_is_redirected_to_login_when_toggling_favorite(): void
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

        $response = $this->post('/books/' . $book->id . '/favorites');
        $response->assertRedirect('/login');
    }
}