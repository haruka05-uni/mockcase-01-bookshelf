<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;


class RankingTest extends TestCase
{
    use RefreshDatabase;

    //ランキング表示
    //ランキングページ（/ranking）が正常に表示され、レビューのある書籍タイトルが含まれること。
    public function test_ranking_page_displays_book_with_reviews(): void
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

        $response = $this->get('/ranking');
        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である');
    }


    //ランキング順序
    //書籍が平均評価の降順で正しく並ぶこと。
    public function test_books_are_ranked_by_average_rating_in_descending_order(): void
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
            'title' => 'コンテナ物語',
            'author' => 'マルク・レビンソン',
            'isbn' => '9784822251468',
            'published_date' => '2007-01-18',
            'description' => '海上輸送コンテナの普及が世界の物流や経済に与えた変化を描いたノンフィクション。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 5,
            'comment' => 'ユーモアがあって面白かったです。',
        ]);

        $response = $this->get('/ranking');

        $response->assertSeeInOrder([
            'コンテナ物語',
            '吾輩は猫である',
        ]);
    }
}
