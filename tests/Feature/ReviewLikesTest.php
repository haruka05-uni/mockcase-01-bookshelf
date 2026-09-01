<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class ReviewLikesTest extends TestCase
{
    use RefreshDatabase;

    //いいね追加
    //認証ユーザーがレビューにいいねを追加でき、review_likesテーブルにレコードが作成されること。
    public function test_authenticated_user_can_remove_like_from_review(): void
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

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        $this->actingAs($user);

        $response = $this->post('/reviews/' . $review->id . '/like');

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    //いいね解除
    //認証ユーザーがレビューのいいねを解除でき、review_likesテーブルからレコードが削除されること。
    public function test_authenticated_user_can_remove_review_from_review_likes(): void
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

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        $this->actingAs($user);
        $review->likedByUsers()->attach($user->id);

        $response = $this->post('/reviews/' . $review->id . '/like');

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    //いいねトグル
    //いいねのトグル（追加→解除→追加）が正しく動作すること。
    public function test_authenticated_user_can_toggle_review_like(): void
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

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        $this->actingAs($user);

        $this->post('/reviews/' . $review->id . '/like');

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $this->post('/reviews/' . $review->id . '/like');

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $this->post('/reviews/' . $review->id . '/like');

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    //ゲスト制限
    //ゲストがいいね操作を行うとログインにリダイレクトされること。
    public function test_guest_is_redirected_to_login_when_toggling_review_like(): void
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

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        $response = $this->post('/reviews/' . $review->id . '/like');
        $response->assertRedirect('/login');
    }
}
