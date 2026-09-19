<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $books = $user->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function toggle(Book $book)
    {
        $user = Auth::user();

        $favoritedBook = $user->favoriteBooks()
            ->where('books.id', $book->id)
            ->exists();

        if ($favoritedBook === true) {
            $user->favoriteBooks()->detach($book->id);
        } else {
            $user->favoriteBooks()->attach($book->id);
        }

        return back();
    }

}
