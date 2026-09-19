<?php

namespace Tests\Feature\Advanced;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    // -----キーワード検索-----
    public function test_books_can_be_searched_by_title_or_author(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => '嫌われる勇気',
            'author' => '岸見一郎・古賀史健',
            'isbn' => '9784478025819',
            'published_date' => '2013-12-13',
            'description' => 'アドラー心理学をもとに、自分らしく生きるための考え方を対話形式で紹介する書籍。',
        ]);

        // 書籍タイトルの一部で検索可能。
        $response = $this->get('/books?keyword=吾輩は');

        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である');
        $response->assertDontSee('嫌われる勇気');

        // 著者名の一部で検索可能。
        $response = $this->get('/books?keyword=岸見');

        $response->assertStatus(200);
        $response->assertSee('嫌われる勇気');
        $response->assertDontSee('吾輩は猫である');

    }

    // -----ジャンル・複合検索-----
    public function test_books_can_be_filtered_by_genre_and_keyword(): void
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
            'title' => '嫌われる勇気',
            'author' => '岸見一郎・古賀史健',
            'isbn' => '9784478025819',
            'published_date' => '2013-12-13',
            'description' => 'アドラー心理学をもとに、自分らしく生きるための考え方を対話形式で紹介する書籍。',
        ]);

        $genre1 = Genre::create([
            'name' => '小説',
        ]);

        $genre2 = Genre::create([
            'name' => '自己啓発',
        ]);

        $book1->genres()->attach($genre1->id);
        $book2->genres()->attach($genre2->id);

        // ジャンルによる絞り込みができる。
        $response = $this->get('/books?genre='.$genre1->id);

        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である');
        $response->assertDontSee('嫌われる勇気');

        // キーワードと同時に指定した場合は両方の条件を満たす書籍のみが表示される。
        $response = $this->get('/books?keyword=岸見&genre='.$genre2->id);

        $response->assertStatus(200);
        $response->assertSee('嫌われる勇気');
        $response->assertDontSee('吾輩は猫である');
    }

    // -----ページネーション-----
    public function test_books_can_be_sorted_by_date_title_or_rating(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $genre = Genre::create([
            'name' => '小説',
        ]);

        $createbooks = Book::factory()->count(11)->create([
            'author' => 'Laravel著者',
        ]);

        foreach ($createbooks as $book) {
            $book->genres()->attach($genre->id);
        }

        // 書籍が10件を超える場合にページネーションされる。
        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10;
        });

        // ページ遷移後も検索・ジャンル・ソート条件が維持されること。
        $response = $this->get(
            '/books?keyword=Laravel&genre='.$genre->id.'&sort=newest'
        );

        $books = $response->viewData('books');

        $this->assertCount(10, $books);
        $this->assertSame(11, $books->total());

        $nextPageUrl = $books->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('keyword=Laravel', $nextPageUrl);
        $this->assertStringContainsString('genre='.$genre->id, $nextPageUrl);
        $this->assertStringContainsString('sort=newest', $nextPageUrl);
        $this->assertStringContainsString('page=2', $nextPageUrl);
    }

    // -----ソート　登録日・タイトル順-----
    public function test_books_can_be_sorted_by_date_or_title(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $oldBook = Book::create([
            'user_id' => $user->id,
            'title' => 'Charlie',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $oldBook->created_at = '2026-07-01';
        $oldBook->save();

        $middleBook = Book::create([
            'user_id' => $user->id,
            'title' => 'Apple',
            'author' => '岸見一郎・古賀史健',
            'isbn' => '9784478025819',
            'published_date' => '2013-12-13',
            'description' => 'アドラー心理学をもとに、自分らしく生きるための考え方を対話形式で紹介する書籍。',
            'created_at' => '2026-08-01',
        ]);

        $middleBook->created_at = '2026-08-01';
        $middleBook->save();

        $newBook = Book::create([
            'user_id' => $user->id,
            'title' => 'Book',
            'author' => 'D・カーネギー',
            'isbn' => '9784422100524',
            'published_date' => '1936-10-01',
            'description' => '良好な人間関係を築き、人の心を動かすための原則を紹介する自己啓発書。',
            'created_at' => '2026-09-01',
        ]);

        $newBook->created_at = '2026-09-01';
        $newBook->save();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $oldBook->id,
            'rating' => 5,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $middleBook->id,
            'rating' => 4,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $newBook->id,
            'rating' => 3,
            'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
        ]);

        // newest：デフォルト
        $response = $this->get('/books');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $newBook->title,
            $middleBook->title,
            $oldBook->title,
        ]);

        // oldest：登録日の古い順
        $response = $this->get('/books?sort=oldest');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $oldBook->title,
            $middleBook->title,
            $newBook->title,
        ]);

        // title：タイトルの昇順
        $response = $this->get('/books?sort=title');

        $books = $response->viewData('books');

        $this->assertSame([
            $middleBook->id, // title:Apple
            $newBook->id, // title:Book
            $oldBook->id, // title:Charlie
        ], $books->pluck('id')->all());

        // rating：評価の降順
        $response = $this->get('/books?sort=rating');

        $books = $response->viewData('books');

        $this->assertSame([
            $oldBook->id, // rating:5
            $middleBook->id, // rating:4
            $newBook->id, // rating:3
        ], $books->pluck('id')->all());

    }
}
