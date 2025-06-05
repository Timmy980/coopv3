<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SavingController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // View savings history (members)
    Route::get('/savings', [SavingController::class, 'index'])->name('savings.index');
    Route::get('/savings/{saving}', [SavingController::class, 'show'])->where('saving', '[0-9]+') // Only match numeric IDs
        ->name('savings.show');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/member.php';
