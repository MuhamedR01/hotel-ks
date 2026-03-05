<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These mirror the original plain PHP backend endpoints so the React
| frontend can continue working with minimal URL changes.
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Products (public read)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show'])->where('id', '[0-9]+');
// Legacy endpoints — same controller, different URL
Route::get('/get_products', [ProductController::class, 'index']);
Route::get('/get_product', function (\Illuminate\Http\Request $request) {
    return app(ProductController::class)->show($request->integer('id'));
});

// Orders (public — guests can place orders)
Route::post('/orders', [OrderController::class, 'store']);
Route::post('/create_order', [OrderController::class, 'store']); // legacy alias

// Authenticated routes (Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
});
