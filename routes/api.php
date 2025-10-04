<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'store'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/verifyotp', [AuthController::class, 'verifyOtp'])->name('verifyOtp');
Route::post('/forgetpassword', [AuthController::class, 'forgetpassword'])->name('forgetpassword');
Route::post('/resetpassword', [AuthController::class, 'resetpassword'])->name('resetpassword');

