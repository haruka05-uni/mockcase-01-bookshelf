<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\API\BookIndexRequest;
use App\Http\Requests\API\BookStoreRequest;
use App\Models\Book;
use App\Http\Resources\BookIndexResource;
use App\Http\Resources\BookShowResource;
use App\Http\Resources\BookStoreResource;

class BookController extends Controller
{
    public function index(BookIndexRequest $request)
    {
        $validated = $request->validated();

        $query = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if (!empty($validated['keyword'])) {
            $query->where(function ($query) use ($validated) {
                $query->where('title', 'like', '%' . $validated['keyword'] . '%')
                    ->orwhere('author', 'like', '%' . $validated['keyword'] . '%');
            });
        }

        if (!empty($validated['genres'])) {
            $query->whereHas('genres', function ($query) use ($validated) {
                $query->where('id', $validated['genres']);
            });
        }

        $perPage = $validated['per_page'] ?? 20;

        $bookIndex = $query->paginate($perPage);

        return BookIndexResource::collection($bookIndex);
    }

    public function store(BookStoreRequest $request)
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

        return (new BookStoreResource($book))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user']);

        return new BookShowResource($book);
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(book $book)
    {
        $book->delete();

        return response()->json(null, 204);
    }
}
