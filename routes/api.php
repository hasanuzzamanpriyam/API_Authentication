<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Auth\AuthController;



// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'store'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/verifyotp', [AuthController::class, 'verifyOtp'])->name('verifyOtp');
Route::post('/forgetpassword', [AuthController::class, 'forgetpassword'])->name('forgetpassword');
Route::post('/resetpassword', [AuthController::class, 'resetpassword'])->name('resetpassword');

Route::middleware('multi.guard')->group(function () {
    Route::get('product', [ProductController::class, 'index'])->name('product.index');
    Route::get('product/{id}', [ProductController::class, 'show'])->name('product.show');
});


Route::middleware(['auth:admin', 'permission:manage-products'])->group(function () {
    Route::post('product', [ProductController::class, 'store'])->name('product.store');
    Route::post('product-update/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
});

Route::middleware('multi.guard')->group(function () {
    Route::get('blogs', [BlogController::class, 'index'])->name('blogs.index');
    Route::get('blogs/{id}', [BlogController::class, 'show'])->name('blogs.show');
});


Route::middleware(['auth:admin', 'permission:manage-blogs'])->group(function () {
    Route::post('blogs', [BlogController::class, 'store'])->name('blogs.store');
    Route::post('blogs/{id}', [BlogController::class, 'update'])->name('blogs.update');
    Route::delete('blogs/{id}', [BlogController::class, 'destroy'])->name('blogs.delete');
});
