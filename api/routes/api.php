<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::resource('users', UserController::class)
        ->only(['index', 'show', 'update', 'destroy']);

    Route::apiResources([
        'authors' => AuthorController::class,
        'books' => BookController::class,
        'borrows' => BorrowController::class,
    ]);
});
