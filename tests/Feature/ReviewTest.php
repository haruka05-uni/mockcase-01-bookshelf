<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    //レビュー投稿
    //認証ユーザーがレビューを投稿でき、reviewsテーブルにレコードが作成されること。
    public function test_authenticated_user_can_create_review(): void
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

        $reviewData = [
            'rating' => 5,
            'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
        ];

        $response = $this->post('/books/' . $book->id . '/reviews', $reviewData);
        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
        ]);
    }

    //レビュー投稿
    //ゲストはログインにリダイレクトされること。
    public function test_guest_user_is_redirected_to_login_when_creating_review(): void
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

        $reviewData = [
            'rating' => 5,
            'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
        ];

        $response = $this->post('/books/' . $book->id . '/reviews', $reviewData);
        $response->assertRedirect('/login');

        $this->assertDatabaseCount('reviews', 0);
    }

    //レビュー投稿
    //ratingのバリデーション（1〜5の範囲）が動作すること。
    public function test_rating_must_be_between_1_and_5(): void
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

        $reviewData = [
            'rating' => 6,
            'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
        ];

        $response = $this->post('/books/' . $book->id . '/reviews', $reviewData);

        $response->assertSessionHasErrors('rating');
    }

    //レビュー編集
    //レビュー投稿者のみが編集フォームを表示・更新できる。
    public function test_review_owner_can_show_edit_form_and_update_review(): void
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

        $updatedReviewData = [
            'rating' => 5,
            'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
        ];

        $response = $this->get('/reviews/' . $review->id . '/edit');
        $response->assertStatus(200);

        $response = $this->put('/reviews/' . $review->id, $updatedReviewData);
        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
        ]);

    }

    //レビュー編集
    //レビュー投稿者以外はレビューを編集できない。403 Forbiddenとなること。
    public function test_non_owner_cannot_update_review(): void
    {
        $bookOwner = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $reviewOwner = User::create([
            'name' => '田中一郎',
            'email' => 'tanaka@example.com',
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

        $review = Review::create([
            'user_id' => $reviewOwner->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        $updatedReviewData = [
            'rating' => 5,
            'comment' => 'ユーモアがあって面白かったです。',
        ];

        $response = $this->put('/reviews/' . $review->id, $updatedReviewData);
        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);
    }

    //レビュー削除
    //レビュー投稿者のみ削除でき、削除後にレビューが消えること。
    public function test_owner_can_delete_review(): void
    {
        $bookOwner = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $reviewOwner = User::create([
            'name' => '田中一郎',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($reviewOwner);

        $book = Book::create([
            'user_id' => $bookOwner->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $review = Review::create([
            'user_id' => $reviewOwner->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        $response = $this->delete('/reviews/' . $review->id);
        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }


    //レビュー削除
    //レビュー投稿者以外はレビューを削除できない。403 Forbiddenとなること。
    public function test_non_owner_cannot_delete_review(): void
    {
        $bookOwner = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $reviewOwner = User::create([
            'name' => '田中一郎',
            'email' => 'tanaka@example.com',
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

        $review = Review::create([
            'user_id' => $reviewOwner->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        $response = $this->delete('/reviews/' . $review->id);
        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
}
