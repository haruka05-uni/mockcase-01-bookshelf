<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Review;
use App\Models\ReadingPlan;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function report()
    {
        $user = Auth::user();
        $reviews = Review::where('user_id', $user->id);
        $completedBooks = ReadingPlan::where('user_id', $user->id)
            ->where('status', 'completed');

        $ratingDistribution = [];

        for ($i = 1; $i < 6; $i++) {
            $ratingDistribution[] =
                Review::where('user_id', $user->id)
                    ->where('rating', $i)
                    ->count();
        }

        $highRatedReviews = Review::with('book')
            ->where('user_id', $user->id)
            ->where('rating', '>=', 4)
            ->orderBy('rating', 'desc')
            ->limit(5)
            ->get();

        $topRatedBooks = $highRatedReviews->map(function ($review) {
            return [
                'id' => $review->book->id,
                'title' => $review->book->title,
                'author' => $review->book->author,
                'rating' => $review->rating,
            ];
        });

        $genreRatings = Genre::whereHas('books.reviews', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->get()
            ->map(function ($genre) use ($user) {

                $reviews = Review::where('user_id', $user->id)
                    ->whereHas('book.genres', function ($query) use ($genre) {
                        $query->where('genres.id', $genre->id);
                    })
                    ->get();

                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'average_rating' => $reviews->avg('rating'),
                    'count' => $reviews->count(),
                ];
            })
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();


        $stats = [
            'summary' => [
                'total_reviews' => $reviews->count(),
                'books_read' => $completedBooks->count(),
                'average_rating' => $reviews->avg('rating') ?? 0,
            ],
            'rating_distribution' => collect($ratingDistribution),
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));

    }
}
