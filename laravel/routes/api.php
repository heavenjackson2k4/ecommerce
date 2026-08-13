<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ShoeVariantController;
use App\Http\Controllers\Admin\ClothVariantController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'apiLogout']);
    Route::get('/me', [AuthController::class, 'getCurrentUser']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Categories
        Route::apiResource('categories', CategoryController::class)->except(['create', 'edit']);

        // Products
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{id}', [ProductController::class, 'show']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);

        // Create shoe & cloth
        Route::post('shoes', [ProductController::class, 'storeShoe']);
        Route::post('clothes', [ProductController::class, 'storeCloth']);

        // Shoe Variants
        Route::get('products/{productId}/shoe-variants', [ShoeVariantController::class, 'index']);
        Route::post('products/{productId}/shoe-variants', [ShoeVariantController::class, 'store']);
        Route::get('shoe-variants/{id}', [ShoeVariantController::class, 'show']);
        Route::put('shoe-variants/{id}', [ShoeVariantController::class, 'update']);
        Route::delete('shoe-variants/{id}', [ShoeVariantController::class, 'destroy']);
        Route::patch('shoe-variants/{id}/stock', [ShoeVariantController::class, 'updateStock']);
        Route::post('shoe-variants/bulk', [ShoeVariantController::class, 'bulkUpdate']);

        // Cloth Variants

        Route::get('products/{productId}/cloth-variants', [ClothVariantController::class, 'index']);
        Route::post('products/{productId}/cloth-variants', [ClothVariantController::class, 'store']);
        Route::get('cloth-variants/{id}', [ClothVariantController::class, 'show']);
        Route::put('cloth-variants/{id}', [ClothVariantController::class, 'update']);
        Route::delete('cloth-variants/{id}', [ClothVariantController::class, 'destroy']);
        Route::patch('cloth-variants/{id}/stock', [ClothVariantController::class, 'updateStock']);
        Route::post('cloth-variants/bulk', [ClothVariantController::class, 'bulkUpdate']);

    });
});