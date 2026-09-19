<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookIndexRequest;
use App\Http\Requests\Api\BookStoreRequest;
use App\Http\Requests\Api\BookUpdateRequest;
use App\Http\Resources\BookIndexCollection;
use App\Http\Resources\BookShowResource;
use App\Http\Resources\BookStoreResource;
use App\Models\Book;

class BookController extends Controller
{
    public function index(BookIndexRequest $request)
    {
        $validated = $request->validated();

        $query = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if (! empty($validated['keyword'])) {
            $query->where(function ($query) use ($validated) {
                $query->where('title', 'like', '%'.$validated['keyword'].'%')
                    ->orwhere('author', 'like', '%'.$validated['keyword'].'%');
            });
        }

        if (! empty($validated['genres'])) {
            $query->whereHas('genres', function ($query) use ($validated) {
                $query->where('genres.id', $validated['genres']);
            });
        }

        $perPage = $validated['per_page'] ?? 20;

        $bookIndex = $query->paginate($perPage);

        return new BookIndexCollection($bookIndex);
    }

    public function store(BookStoreRequest $request)
    {
        $validated = $request->validated();

        $book = Book::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        $book->genres()->attach($validated['genres']);

        $book->load('genres');

        return (new BookStoreResource($book))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user']);

        return new BookShowResource($book);
    }

    public function update(BookUpdateRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $genres = $validated['genres'] ?? [];

        unset($validated['genres']);

        $book->update($validated);

        $book->genres()->sync($genres);

        $book->load('genres');

        return (new BookStoreResource($book))
            ->response()
            ->setStatusCode(200);

    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json(null, 204);
    }
}
