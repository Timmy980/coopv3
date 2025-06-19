<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\Admin\MemberAccountTypeController;
use App\Http\Controllers\Admin\CooperativeAccountController;
use App\Http\Controllers\Admin\MemberAccountController;
use App\Http\Controllers\Admin\SavingBulkController;
use App\Http\Controllers\Admin\SavingController;
use App\Http\Controllers\Admin\WithdrawalRequestController;
use App\Http\Middleware\AdminMiddleware;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', AdminMiddleware::class])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | User Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->name('users.')->group(function () {
        // User Approvals
        Route::prefix('approvals')->name('approvals.')->group(function () {
            Route::get('/', [UserApprovalController::class, 'index'])->name('index');
            Route::patch('{user}/approve', [UserApprovalController::class, 'approve'])->name('approve');
            Route::patch('{user}/reject', [UserApprovalController::class, 'reject'])->name('reject');
        });
        
        // Role and Permission Management
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RolePermissionController::class, 'index'])->name('index');
            Route::post('/', [RolePermissionController::class, 'createRole'])->name('store');
            Route::put('{role}', [RolePermissionController::class, 'updateRole'])->name('update');
            Route::delete('{role}', [RolePermissionController::class, 'deleteRole'])->name('destroy');
            Route::patch('{user}/assign', [RolePermissionController::class, 'assignRole'])->name('assign');
            Route::patch('{user}/unassign', [RolePermissionController::class, 'unassignRole'])->name('unassign');
        });
        
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::post('/', [RolePermissionController::class, 'createPermission'])->name('store');
            Route::delete('{permission}', [RolePermissionController::class, 'deletePermission'])->name('destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Account Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('accounts')->name('accounts.')->group(function () {
        // Account Types
        Route::prefix('types')->name('types.')->group(function () {
            Route::get('/', [MemberAccountTypeController::class, 'index'])->name('index');
            Route::post('/', [MemberAccountTypeController::class, 'store'])->name('store');
            Route::put('{accountType}', [MemberAccountTypeController::class, 'update'])->name('update');
            Route::delete('{accountType}', [MemberAccountTypeController::class, 'destroy'])->name('destroy');
        });
        
        // Cooperative Accounts
        Route::prefix('cooperative')->name('cooperative.')->group(function () {
            Route::get('/', [CooperativeAccountController::class, 'index'])->name('index');
            Route::get('create', [CooperativeAccountController::class, 'create'])->name('create');
            Route::post('/', [CooperativeAccountController::class, 'store'])->name('store');
            Route::get('{cooperativeAccount}', [CooperativeAccountController::class, 'show'])->name('show');
            Route::get('{cooperativeAccount}/edit', [CooperativeAccountController::class, 'edit'])->name('edit');
            Route::put('{cooperativeAccount}', [CooperativeAccountController::class, 'update'])->name('update');
            Route::delete('{cooperativeAccount}', [CooperativeAccountController::class, 'destroy'])->name('destroy');
            Route::patch('{cooperativeAccount}/toggle-status', [CooperativeAccountController::class, 'toggleStatus'])->name('toggle-status');
        });
        
        // Member Accounts
        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', [MemberAccountController::class, 'index'])->name('index');
            Route::post('/', [MemberAccountController::class, 'store'])->name('store');
            Route::get('{memberAccount}', [MemberAccountController::class, 'show'])->name('show');
            Route::put('{memberAccount}', [MemberAccountController::class, 'update'])->name('update');
            Route::delete('{memberAccount}', [MemberAccountController::class, 'destroy'])->name('destroy');
            Route::patch('{memberAccount}/toggle-status', [MemberAccountController::class, 'toggleStatus'])->name('toggle-status');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Savings Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('savings')->name('savings.')->group(function () {
        // Savings Management
        Route::get('/', [SavingController::class, 'index'])->name('index');
        Route::get('{saving}', [SavingController::class, 'show'])->name('show');

        // Single Entry Management
        Route::post('single', [SavingController::class, 'storeSingleEntry'])->name('single.store');
        
        // Bulk Operations
        Route::prefix('bulk')->name('bulk.')->group(function () {
            Route::get('/', [SavingBulkController::class, 'index'])->name('index');
            Route::get('upload', [SavingBulkController::class, 'showUploadForm'])->name('create');
            Route::post('upload', [SavingBulkController::class, 'upload'])->name('store');
            Route::get('template', [SavingBulkController::class, 'downloadTemplate'])->name('template');
            Route::get('{batch}', [SavingBulkController::class, 'show'])->name('show');
        });
        
        // Approval Management
        Route::prefix('approvals')->name('approvals.')->group(function () {
            Route::get('pending', [SavingController::class, 'pendingApproval'])->name('pending');
            Route::patch('{saving}/approve', [SavingController::class, 'approve'])->name('approve');
            Route::patch('{saving}/reject', [SavingController::class, 'reject'])->name('reject');
        });
        
        // Reports
        Route::get('reports', [SavingController::class, 'reports'])->name('reports');
    });

    /*
    |--------------------------------------------------------------------------
    | Withdrawal Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [WithdrawalRequestController::class, 'index'])->name('index');
        Route::get('{withdrawalRequest}', [WithdrawalRequestController::class, 'show'])->name('show');
        Route::patch('{withdrawalRequest}/approve', [WithdrawalRequestController::class, 'approve'])->name('approve');
        Route::patch('{withdrawalRequest}/reject', [WithdrawalRequestController::class, 'reject'])->name('reject');
    });
});