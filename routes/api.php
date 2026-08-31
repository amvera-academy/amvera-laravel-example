<?php

use App\Http\Controllers\ItemsController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [ItemsController::class, 'health']);
Route::get('/items', [ItemsController::class, 'index']);
Route::post('/items', [ItemsController::class, 'store']);
Route::delete('/items/{id}', [ItemsController::class, 'destroy']);
