<?php

use App\Modules\Circulation\Http\Controllers\DashboardController;
use App\Modules\Circulation\Http\Controllers\FineController;
use App\Modules\Circulation\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:sanctum', 'role:admin,pustakawan'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('loans/{loan}', [LoanController::class, 'show'])->name('loans.show')->whereNumber('loan');
    Route::post('loans/{loan}/return', [LoanController::class, 'returnLoan'])->name('loans.return');

    Route::get('fines', [FineController::class, 'index'])->name('fines.index');
    Route::get('fines/export', [FineController::class, 'export'])->name('fines.export');
    Route::post('fines/{fine}/pay', [LoanController::class, 'payFine'])->name('fines.pay');
});
