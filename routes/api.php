<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthorsController;
use App\Http\Controllers\Api\BooksController;
use App\Http\Controllers\Api\LoansController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rotas sem verificação
Route::post('register', [ApiController::class, 'register']);
Route::post('login', [ApiController::class, 'login']);

// Rotas protegidas por autenticação
Route::group(['middleware' => ['auth:api']], function () {
    Route::get('profile', [ApiController::class, 'profile']);
    Route::get('refresh-token', [ApiController::class, 'refreshToken']);
    Route::get('logout', [ApiController::class, 'logout']);

    Route::apiResource('authors', AuthorsController::class);
    Route::apiResource('books', BooksController::class);
    Route::apiResource('loans', LoansController::class);
});
