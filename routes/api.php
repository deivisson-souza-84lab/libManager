<?php

use App\Http\Controllers\Api\ApiController;
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
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
