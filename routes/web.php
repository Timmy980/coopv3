<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\RolePermissionController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Role and Permission Management Routes
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RolePermissionController::class, 'createRole'])->name('roles.create');
    Route::put('/roles/{role}', [RolePermissionController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles/{role}', [RolePermissionController::class, 'deleteRole'])->name('roles.delete');
    
    Route::post('/permissions', [RolePermissionController::class, 'createPermission'])->name('permissions.create');
    Route::delete('/permissions/{permission}', [RolePermissionController::class, 'deletePermission'])->name('permissions.delete');
    
    Route::post('/assign-role', [RolePermissionController::class, 'assignRole'])->name('roles.assign');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
