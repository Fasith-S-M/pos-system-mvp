<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return view('welcome');
});

// For MVP we place APIs here to avoid CORS issues if they run on same port, 
// but since frontend is on different port, we'll need CORS config.
// Also we added validateCsrfTokens exception for api/*
Route::post('/api/login', [AuthController::class, 'login']);
Route::get('/api/products', [ProductController::class, 'index']);
Route::post('/api/orders', [OrderController::class, 'store']);
