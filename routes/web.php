<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserApprovalController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Admin\MemberAccountTypeController;
use App\Http\Controllers\Admin\CooperativeAccountController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Role and Permission Management Routes
Route::middleware(['auth', 'verified', AdminMiddleware::class])->group(function () {
    // User Approval Routes
    Route::get('/user-approvals', [UserApprovalController::class, 'index'])->name('user-approvals.index');
    Route::post('/user-approvals/{user}/approve', [UserApprovalController::class, 'approve'])->name('user-approvals.approve');
    Route::post('/user-approvals/{user}/reject', [UserApprovalController::class, 'reject'])->name('user-approvals.reject');

    // Role and Permission Management Routes
    Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RolePermissionController::class, 'createRole'])->name('roles.create');
    Route::put('/roles/{role}', [RolePermissionController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles/{role}', [RolePermissionController::class, 'deleteRole'])->name('roles.delete');
    
    Route::post('/permissions', [RolePermissionController::class, 'createPermission'])->name('permissions.create');
    Route::delete('/permissions/{permission}', [RolePermissionController::class, 'deletePermission'])->name('permissions.delete');
    
    Route::post('/assign-role', [RolePermissionController::class, 'assignRole'])->name('roles.assign');
    Route::post('/unassign-role', [RolePermissionController::class, 'unassignRole'])->name('roles.unassign');

    // Account Type Management Routes
    Route::get('/account-types', [MemberAccountTypeController::class, 'index'])->name('account-types.index');
    Route::post('/account-types', [MemberAccountTypeController::class, 'store'])->name('account-types.store');
    Route::put('/account-types/{accountType}', [MemberAccountTypeController::class, 'update'])->name('account-types.update');
    Route::delete('/account-types/{accountType}', [MemberAccountTypeController::class, 'destroy'])->name('account-types.destroy');

    // Cooperative Accounts Management
    Route::resource('cooperative-accounts', CooperativeAccountController::class)->names([
        'index' => 'cooperative_accounts.index',
        'create' => 'cooperative_accounts.create',
        'store' => 'cooperative_accounts.store',
        'show' => 'cooperative_accounts.show',
        'edit' => 'cooperative_accounts.edit',
        'update' => 'cooperative_accounts.update',
        'destroy' => 'cooperative_accounts.destroy',
    ]);

    // Additional route for toggling account status
    Route::patch('cooperative-accounts/{cooperative_account}/toggle-status', [CooperativeAccountController::class, 'toggleStatus'])
         ->name('cooperative_accounts.toggle_status');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
