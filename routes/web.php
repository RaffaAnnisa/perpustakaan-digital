<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Route modul (books, members, loans) dimuat oleh Service Provider tiap modul.

// '/' (dasbor) didaftarkan oleh modul Circulation.

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum')
    ->name('logout');
