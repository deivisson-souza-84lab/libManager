<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthorsController;
use App\Http\Controllers\Api\BooksController;
use App\Http\Controllers\Api\LoansController;
use App\Http\Middleware\AdminMiddleware;
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

/**
 * Aqui eu adicionei o middleware `AdminMiddleware` apenas para as rotas 
 * `store`, `update` e `destroy` dos controllers `AuthorsController`,
 * `BooksController` e `LoansController`.
 */
Route::middleware([AdminMiddleware::class, 'auth:api'])->group(function () {
  Route::post('authors', [AuthorsController::class, 'store']);
  Route::put('authors/{author}', [AuthorsController::class, 'update']);
  Route::delete('authors/{author}', [AuthorsController::class, 'destroy']);

  Route::post('books', [BooksController::class, 'store']);
  Route::put('books/{book}', [BooksController::class, 'update']);
  Route::delete('books/{book}', [BooksController::class, 'destroy']);

  Route::post('loans', [LoansController::class, 'store']);
  Route::put('loans/{loan}', [LoansController::class, 'update']);
  Route::delete('loans/{loan}', [LoansController::class, 'destroy']);
});
