<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RankingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('auth')->group(function () {
    //書籍関連
    Route::resource('books', BookController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy']);

    //ジャンル関連
    Route::resource('genres', GenreController::class);

    //レビュー関連
    Route::resource('reviews', ReviewController::class)
        ->only(['edit', 'update', 'destroy']);

    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    //お気に入り関連
    Route::resource('favorites', FavoriteController::class)
        ->only(['index']);

    Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])->name('reviews.like');
});

Route::resource('books', BookController::class)
    ->only(['index', 'show']);

Route::resource('ranking', RankingController::class)
    ->only(['index']);


