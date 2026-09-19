<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $genres = Genre::all();
        $query = Book::query()
            ->with('genres')
            ->withAvg('reviews', 'rating');

        $validated = $request->validate([
            'keyword' => 'nullable|string|max:255',
            'genre' => 'nullable|integer|exists:genres,id',
            'sort' => 'nullable|in:newest,oldest,title,rating',
        ]);

        if (! empty($validated['keyword'])) {
            $query->where(function ($query) use ($validated) {
                $query->where('title', 'like', '%'.$validated['keyword'].'%')
                    ->orWhere('author', 'like', '%'.$validated['keyword'].'%');
            });
        }

        if (! empty($validated['genre'])) {
            $query->whereHas('genres', function ($query) use ($validated) {
                $query->where('genres.id', $validated['genre']);
            });
        }

        $sort = $validated['sort'] ?? 'newest';

        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc');
                break;

            case 'title':
                $query->orderBy('title', 'asc');
                break;

            case 'rating':
                $query->orderByRaw('reviews_avg_rating IS NULL')
                    ->orderBy('reviews_avg_rating', 'desc');
                break;

            default:
                $query->orderBy('created_at', 'desc')
                    ->orderBy('id', 'asc');
                break;
        }

        $books = $query
            ->paginate(10)
            ->withQueryString();

        return view('books.index', compact('books', 'genres'));
    }

    public function isbnSearch($isbn)
    {
        // ISBNが13桁の数字かチェック
        if (! preg_match('/^\d{13}$/', $isbn)) {
            return response()->json([
                'error' => 'ISBNは13桁で入力してください。',
            ], 400);
        }

        try {
            $response = Http::get(
                'https://www.googleapis.com/books/v1/volumes',
                [
                    'q' => 'isbn:'.$isbn,
                    'key' => config('services.google_books.api_key'),
                ]
            );

            // クォータ超過
            if ($response->status() === 429) {
                return response()->json([
                    'error' => 'Google Books API のクォータを超過しました。.env に GOOGLE_BOOKS_API_KEY を設定してください。',
                ], 429);
            }

            // その他のAPIエラー
            if ($response->failed()) {
                return response()->json([
                    'error' => 'API通信エラーが発生しました。',
                ], 500);
            }

            $data = $response->json();

            // 該当する書籍がない
            if (empty($data['items'])) {
                return response()->json([
                    'error' => '書籍が見つかりませんでした。',
                ], 404);
            }

            $volumeInfo = $data['items'][0]['volumeInfo'];

            return response()->json([
                'title' => $volumeInfo['title'] ?? '',
                'author' => isset($volumeInfo['authors'])
                    ? implode(', ', $volumeInfo['authors'])
                    : '',
                'published_date' => $volumeInfo['publishedDate'] ?? '',
                'description' => $volumeInfo['description'] ?? '',
                'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'API通信エラーが発生しました。',
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        $book->genres()->attach($validated['genres']);

        return redirect()->route('books.index')->with('success', '書籍を登録しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->load('genres');
        $book->loadCount('reviews');

        return view('books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        $this->authorize('update', $book);
        $genres = Genre::all();

        return view('books.edit', compact('genres', 'book'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $book->update([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        $book->genres()->sync($validated['genres']);

        return redirect()->route('books.index')->with('success', '書籍情報を更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍を削除しました');
    }
}
