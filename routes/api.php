<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProductController;



// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'store'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/verifyotp', [AuthController::class, 'verifyOtp'])->name('verifyOtp');
Route::post('/forgetpassword', [AuthController::class, 'forgetpassword'])->name('forgetpassword');
Route::post('/resetpassword', [AuthController::class, 'resetpassword'])->name('resetpassword');


Route::get('product', [ProductController::class, 'index'])->name('product.index');
Route::post('product', [ProductController::class, 'store'])->name('product.store');
Route::post('product-update/{id}', [ProductController::class, 'update'])->name('product.update'); // Use POST for form-data updates
Route::get('product/{id}', [ProductController::class, 'show'])->name('product.show');
Route::delete('product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
