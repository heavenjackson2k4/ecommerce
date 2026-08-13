<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VariantController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public API Routes - không cần authentication
Route::post('/login', [AuthController::class, 'login']);

// Protected API Routes - cần Sanctum token
Route::middleware('auth:sanctum')->group(function () {
    // User Routes
    Route::post('/logout', [AuthController::class, 'apiLogout']);
    Route::get('/me', [AuthController::class, 'getCurrentUser']);

    // Admin Routes - cần token + admin role
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Categories
        Route::apiResource('categories', CategoryController::class)->except(['create', 'edit']);

        // Products (index, show, update, delete)
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{id}', [ProductController::class, 'show']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);

        // Create shoe & cloth
        Route::post('shoes', [ProductController::class, 'storeShoe'])->name('api.admin.clothes.store');
        Route::post('clothes', [ProductController::class, 'storeCloth'])->name('api.admin.shoes.store'); 

        // Variants
        Route::get('products/{productId}/variants', [VariantController::class, 'index']);
        Route::post('products/{productId}/variants', [VariantController::class, 'store']);
        Route::get('variants/{id}', [VariantController::class, 'show']);
        Route::put('variants/{id}', [VariantController::class, 'update']);
        Route::delete('variants/{id}', [VariantController::class, 'destroy']);
        Route::patch('variants/{id}/stock', [VariantController::class, 'updateStock']);
        Route::post('variants/bulk', [VariantController::class, 'bulkUpdate']);
    });

    // Customer Routes - cần token + customer role
    Route::middleware('role:customer')->prefix('customer')->group(function () {
        // Future customer routes here
    });
});