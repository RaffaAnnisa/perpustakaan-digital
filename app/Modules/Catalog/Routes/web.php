<?php

use App\Modules\Catalog\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

// loadRoutesFrom() tidak otomatis memberi middleware "web", jadi dibungkus manual
Route::middleware('web')->group(function () {
    Route::middleware(['auth:sanctum', 'role:admin,pustakawan'])->group(function () {
        Route::resource('books', BookController::class)->except(['show', 'destroy']);
    });

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::delete('books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    });
});
