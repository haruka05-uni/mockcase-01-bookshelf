<?php

namespace Tests\Feature\Advanced;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\ReadingPlan;


class ReportTest extends TestCase
{

    use RefreshDatabase;
    // ----- ゲストがレポートページへアクセスするとログイン画面へリダイレクトされること -----
    public function test_guest_is_redirected_to_login_from_report_page(): void
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }

    // ----- 認証ユーザーの統計情報が正しく計算されること -----
    public function test_authenticated_user_report_statistics_are_calculated_correctly(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $books = Book::factory()->count(6)->create();
        $ratings = [5, 5, 5, 4, 4, 4];

        //評価4以上の本を6冊作る
        foreach ($books as $index => $book) {
            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => $ratings[$index],
            ]);
        }

        //ジャンルを紐づける
        $genre1 = Genre::create(
            [
                'name' => '小説',
            ]
        );

        $genre2 = Genre::create(
            [
                'name' => 'ビジネス',
            ]
        );

        $genre3 = Genre::create(
            [
                'name' => '自己啓発',
            ]
        );

        $genreIds = [
            $genre1->id,
            $genre1->id,
            $genre2->id,
            $genre2->id,
            $genre3->id,
            $genre3->id,
        ];

        foreach ($books as $index => $book) {
            $book->genres()->attach($genreIds[$index]);
        }

        //読了済みの読書計画も用意する

        $completedBooks = $books->random(3);

        foreach ($completedBooks as $completedBook) {
            ReadingPlan::create([
                'user_id' => $user->id,
                'book_id' => $completedBook->id,
                'target_date' => '2026-09-01',
                'completed_at' => '2026-08-30',
                'status' => 'completed',
            ]);
        }

        //認証ユーザーの統計情報が正しく計算されること。
        $response = $this->get('/reports');
        $response->assertStatus(200);

        $stats = $response->viewData('stats');

        //summary
        $this->assertSame(6, $stats['summary']['total_reviews']);
        $this->assertSame(3, $stats['summary']['books_read']);
        $this->assertEquals(4.5, $stats['summary']['average_rating']);

        //rating_distribution
        $this->assertSame([0, 0, 0, 3, 3], $stats['rating_distribution']->all());

        //top_rated_books (期待する件数)
        $topRatedBooks = $stats['top_rated_books'];
        $this->assertCount(5, $topRatedBooks);

        //top_rated_books (期待する評価)
        $topRatedBooks = $stats['top_rated_books'];
        $this->assertSame([5, 5, 5, 4, 4], $topRatedBooks->pluck('rating')->all());

        //genre_ratings (期待する件数)
        $this->assertCount(3, $stats['genre_ratings']);

        //genre_ratings (平均評価が5・4.5・4の順になっている)
        $genreRatings = $stats['genre_ratings'];
        $this->assertEquals([5.0, 4.5, 4.0], $genreRatings->pluck('average_rating')->all());

        //genre_ratings (各ジャンルのレビュー件数が2・2・2)
        $this->assertSame([2, 2, 2], $genreRatings->pluck('count')->all());

    }

    //----- レビューがないユーザーの場合、各統計が0/空として安全に処理されること。 -----
    public function test_report_statistics_are_zero_or_empty_when_user_has_no_reviews(): void
    {

        $anotherUser = User::create([
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($anotherUser);

        $response = $this->get('/reports');
        $response->assertStatus(200);

        $stats = $response->viewData('stats');

        //summary
        $this->assertSame(0, $stats['summary']['total_reviews']);
        $this->assertSame(0, $stats['summary']['books_read']);
        $this->assertSame(0, $stats['summary']['average_rating']);

        //rating_distribution
        $this->assertSame([0, 0, 0, 0, 0], $stats['rating_distribution']->all());

        //top_rated_books
        $this->assertTrue($stats['top_rated_books']->isEmpty());

        //genre_ratings
        $this->assertTrue($stats['genre_ratings']->isEmpty());
    }
}
