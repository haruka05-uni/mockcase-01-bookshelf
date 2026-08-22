<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Genre;
use App\Models\Book;
use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::all();

        return view('genres.index', compact('genres'));
    }

    public function create()
    {
        return view('genres.create');
    }

    public function store(StoreGenreRequest $request)
    {
        $validated = $request->validated();
        Genre::create($validated);

        return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました。');
    }

    public function show(Genre $genre)
    {
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    public function update(UpdateGenreRequest $request, Genre $genre)
    {
        $validated = $request->validated();
        $genre->update($validated);

        return redirect()->route('genres.index');
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();

        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました。');
    }
}
