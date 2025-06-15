<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SavingController;
use App\Models\MemberAccount;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Member\WithdrawalRequestController;
use App\Http\Controllers\Member\AccountController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // View savings history (members)
    Route::get('/savings', [SavingController::class, 'index'])->name('savings.index');
    Route::get('/savings/{saving}', [SavingController::class, 'show'])->where('saving', '[0-9]+') // Only match numeric IDs
        ->name('savings.show');

    

});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/member.php';
