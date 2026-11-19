<?php

use App\Modules\Membership\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:sanctum', 'role:admin'])->group(function () {
    Route::get('members/{member}/card', [MemberController::class, 'card'])->name('members.card');
    Route::resource('members', MemberController::class)->except(['show']);
});
