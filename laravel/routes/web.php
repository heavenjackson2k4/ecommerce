<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ProductController;

// Route::get('/', function () {
//     return view('welcome');
// });



// Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function (){
    Route::get('/dashboard', function(){
        return view('admin.dashboard');
    })->name('admin.dashboard');

    //  Route::get('/dashboard', function () {
    //     return view('admin.dashboard');
    // })->name('admin.dashboard');

    // Nhập quần áo
    Route::get('/nhap-quan-ao', function () {
        return view('admin.nhap-quan-ao');
    })->name('admin.nhap-quan-ao');

    // Nhập giày
    Route::get('/nhap-giay', function () {
        return view('admin.nhap-giay');
    })->name('admin.nhap-giay');


    Route::post('/shoes', [ProductController::class, 'storeShoe'])->name('admin.shoes.store');
    Route::post('/clothes', [ProductController::class, 'storeCloth'])->name('admin.clothes.store');
});

Route::middleware(['auth', 'role:customer'])->prefix('customer')->group(function () {
    Route::get('/dashboard', function () {
        return view('customer.dashboard');
    })->name('customer.dashboard');
    // future customer routes here
});


// Ví dụ route dashboard yêu cầu xác thực
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');