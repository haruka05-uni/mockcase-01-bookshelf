<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    // 閲覧系
    Route::apiResource('books', BookController::class)
        ->only(['index', 'show'])
        ->missing(function (Request $request) {
            return response()->json([
                'message' => '書籍が見つかりませんでした。',
            ], 404);
        });

    // 書き込み系
    Route::middleware('auth:sanctum')->group(function () {

        Route::apiResource('books', BookController::class)
            ->only(['store', 'update', 'destroy'])
            ->missing(function (Request $request) {
                return response()->json([
                    'message' => '書籍が見つかりませんでした。',
                ], 404);
            });

    });

});
