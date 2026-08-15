<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Admin\ImageUploadController;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Nhập quần áo - gọi controller để truyền categories
    Route::get('/nhap-quan-ao', [ProductController::class, 'createCloth'])->name('admin.nhap-quan-ao');
    
    // Nhập giày - gọi controller để truyền categories
    Route::get('/nhap-giay', [ProductController::class, 'createShoe'])->name('admin.nhap-giay');

    Route::post('/shoes', [ProductController::class, 'storeShoe'])->name('admin.shoes.store');
    Route::post('/clothes', [ProductController::class, 'storeCloth'])->name('admin.clothes.store');

    Route::post('/upload-image', [ImageUploadController::class, 'upload'])->name('admin.upload.image');
});

Route::middleware(['auth', 'role:customer'])->prefix('customer')->group(function () {
    Route::get('/dashboard', function () {
        return view('customer.dashboard');
    })->name('customer.dashboard');

// Customer Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [CustomerProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [CustomerProductController::class, 'show'])->name('products.show');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');