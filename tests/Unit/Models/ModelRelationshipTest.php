<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    // Book関係
    // 1つの書籍は、1人の登録ユーザーに紐づくこと。（belongsTo）
    public function test_book_belongs_to_user(): void
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

        $this->assertEquals(
            $user->id,
            $book->user->id
        );

    }

    // Book関係
    // 1つの書籍から、紐づく複数のレビューが正しく取得できること。（hasMany）
    public function test_book_has_many_reviews(): void
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

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        $this->assertCount(2, $book->reviews);

    }

    // Book関係
    // 1つの書籍に複数のジャンルが紐づくこと。（belongsToMany）
    public function test_book_belongs_to_many_genres(): void
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

        $genre1 = Genre::create([
            'name' => '小説',
        ]);

        $genre2 = Genre::create([
            'name' => 'ビジネス',
        ]);

        $book->genres()->attach([
            $genre1->id,
            $genre2->id,
        ]);

        $this->assertCount(2, $book->genres);
    }

    // Book関係
    // 1つの書籍は複数のユーザーにお気に入り書籍として紐づくこと。（belongsToMany）
    public function test_book_belongs_to_many_favorited_by_users(): void
    {
        $user = User::create([
            'name' => '田中一郎',
            'email' => 'tanaka@example.com',
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

        $user1 = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $user2 = User::create([
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
        ]);

        $book->favoritedByUsers()->attach([
            $user1->id,
            $user2->id,
        ]);

        $this->assertCount(2, $book->favoritedByUsers);
    }

    // User関係
    // 1人のユーザーから、自身の登録した複数の書籍を取得できること。（hasMany）
    public function test_user_has_many_books(): void
    {
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

        $this->assertCount(2, $user->books);
    }

    // User関係
    // 1人のユーザーから、自身の投稿した複数のレビューを取得できること。（hasMany）
    public function test_user_has_many_reviews(): void
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

        $user1 = User::create([
            'name' => '田中一郎',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('password'),
        ]);

        Review::create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
        ]);

        Review::create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        $this->assertCount(2, $user1->reviews);
    }

    // User関係
    // 1人のユーザーに複数の書籍がお気に入りとして紐づくこと。（belongsToMany）
    public function test_user_belongs_to_many_favorite_books(): void
    {
        $user = User::create([
            'name' => '田中一郎',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('password'),
        ]);

        $user1 = User::create([
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
        ]);

        $book1 = Book::create([
            'user_id' => $user1->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $book2 = Book::create([
            'user_id' => $user1->id,
            'title' => '人を動かす',
            'author' => 'D・カーネギー',
            'isbn' => '9784422100524',
            'published_date' => '1936-10-01',
            'description' => '良好な人間関係を築き、人の心を動かすための原則を紹介する自己啓発書。',
        ]);

        $user->favoriteBooks()->attach([
            $book1->id,
            $book2->id,
        ]);

        $this->assertCount(2, $user->favoriteBooks);
    }

    // User関係
    // 1人のユーザーに複数のレビューがいいねとして紐づくこと。（belongsToMany）
    public function test_user_belongs_to_many_liked_reviews(): void
    {
        $user = User::create([
            'name' => '田中一郎',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('password'),
        ]);

        $bookOwner = User::create([
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
        ]);

        $book1 = Book::create([
            'user_id' => $bookOwner->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $book2 = Book::create([
            'user_id' => $bookOwner->id,
            'title' => '人を動かす',
            'author' => 'D・カーネギー',
            'isbn' => '9784422100524',
            'published_date' => '1936-10-01',
            'description' => '良好な人間関係を築き、人の心を動かすための原則を紹介する自己啓発書。',
        ]);

        $review1 = Review::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
            'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
        ]);

        $review2 = Review::create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        $liker = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $liker->likedReviews()->attach([
            $review1->id,
            $review2->id,
        ]);

        $this->assertCount(2, $liker->likedReviews);
    }

    // Review関係
    // 1つのレビューは、1つの投稿ユーザーに紐づく。（belongsTo）
    public function test_review_belongs_to_user(): void
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
            'rating' => 5,
            'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
        ]);

        $this->assertEquals(
            $user->id,
            $review->user->id,
        );

    }

    // Review関係
    // 1つのレビューは、1つの書籍に紐づく。（belongsTo）
    public function test_review_belongs_to_book(): void
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
            'rating' => 5,
            'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
        ]);

        $this->assertEquals(
            $book->id,
            $review->book->id,
        );

    }

    // Review関係
    // 1つのレビューには複数のユーザーのいいねが紐づいている。（belongsToMany）
    public function test_review_belongs_to_many_liked_by_users(): void
    {
        $user = User::create([
            'name' => '佐藤美咲',
            'email' => 'sato@example.com',
            'password' => Hash::make('password'),
        ]);

        $bookOwner = User::create([
            'name' => '田中一郎',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('password'),
        ]);

        $liker1 = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $liker2 = User::create([
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

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
        ]);

        $review->likedByUsers()->attach([
            $liker1->id,
            $liker2->id,
        ]);

        $this->assertCount(2, $review->likedByUsers);

    }
}
