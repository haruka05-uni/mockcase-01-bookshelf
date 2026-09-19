<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Book $book)
    {
        $user = Auth::user();

        $validated = $request->validated();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()->route('books.show', $book)->with('success', 'レビューが投稿されました。');
    }

    public function edit(Review $review)
    {
        $this->authorize('update', $review);

        $review->load('book');

        return view('reviews.edit', compact('review'));
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $book = $review->book;
        $validated = $request->validated();

        $review->update($validated);

        return redirect()->route('books.show', $book)->with('success', 'レビューを更新しました。');
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);
        $book = $review->book;
        $review->delete();

        return redirect()->route('books.show', $book)->with('success', 'レビューを削除しました。');
    }

    public function like(Review $review)
    {
        $user = Auth::user();

        $likedreview = $review->likedByUsers()
            ->where('user_id', $user->id)
            ->exists();

        if ($likedreview === true) {
            $review->likedByUsers()->detach($user->id);
        } else {
            $review->likedByUsers()->attach($user->id);
        }

        return back();
    }
}
