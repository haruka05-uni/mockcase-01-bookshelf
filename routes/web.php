<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/books');

Route::middleware('auth')->group(function () {

    // ISBN検索
    Route::get('/books/isbn/{isbn}', [BookController::class, 'isbnSearch'])
        ->name('books.isbn-search');

    // 書籍関連
    Route::resource('books', BookController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy']);

    // ジャンル関連
    Route::resource('genres', GenreController::class);

    // レビュー関連
    Route::resource('reviews', ReviewController::class)
        ->only(['edit', 'update', 'destroy']);

    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // お気に入り関連
    Route::resource('favorites', FavoriteController::class)
        ->only(['index']);

    Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])->name('reviews.like');

    // 書籍計画関連
    Route::resource('reading-plans', ReadingPlanController::class);

    Route::post(
        '/reading-plans/{readingPlan}/complete',
        [ReadingPlanController::class, 'complete']
    )->name('reading-plans.complete');

    // マイ読書レポート
    Route::get('/reports', [ReportController::class, 'report'])->name('reports.index');

    // 通知関連
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'read'])->name('notifications.read');

});

Route::resource('books', BookController::class)
    ->only(['index', 'show']);

Route::resource('ranking', RankingController::class)
    ->only(['index']);
