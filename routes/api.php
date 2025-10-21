<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrinoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('trino')->group(function () {
    Route::get('/test', [TrinoController::class, 'test']);
    Route::post('/query', [TrinoController::class, 'executeQuery']);
    Route::get('/users', [TrinoController::class, 'getUsers']);
    Route::get('/products', [TrinoController::class, 'getProducts']);
});

