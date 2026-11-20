<?php

use App\Modules\Membership\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:sanctum', 'role:admin'])->group(function () {
    Route::get('members/{member}/card', [MemberController::class, 'card'])->name('members.card');
    Route::resource('members', MemberController::class)->except(['show']);
});

// --- Jenis Keanggotaan (khusus admin) ---
\Illuminate\Support\Facades\Route::middleware(['web', 'auth:sanctum', 'role:admin'])->group(function () {
    \Illuminate\Support\Facades\Route::resource('membership-types', \App\Modules\Membership\Http\Controllers\MembershipTypeController::class)
        ->parameters(['membership-types' => 'membershipType'])
        ->except(['show']);
});
