<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserApprovalController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Admin\MemberAccountTypeController;
use App\Http\Controllers\Admin\CooperativeAccountController;
use App\Http\Controllers\MemberAccountController;
use App\Http\Controllers\SavingBulkController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\Admin\WithdrawalRequestController;


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

    // Member Accounts CRUD
    Route::get('/member-accounts', [MemberAccountController::class, 'index'])->name('member_accounts.index');
    Route::get('/member-accounts/{memberAccount}', [MemberAccountController::class, 'show'])->name('member_accounts.show');
    Route::post('/member-accounts', [MemberAccountController::class, 'store'])->name('member_accounts.store');
    Route::put('/member-accounts/{memberAccount}', [MemberAccountController::class, 'update'])->name('member_accounts.update');
    Route::delete('/member-accounts/{memberAccount}', [MemberAccountController::class, 'destroy'])->name('member_accounts.destroy');
    Route::patch('/member-accounts/{memberAccount}/toggle-status', [MemberAccountController::class, 'toggleStatus'])->name('member_accounts.toggle_status');
    
    /*
    |--------------------------------------------------------------------------
    | Savings Routes
    |--------------------------------------------------------------------------
    */
    // Savings Routes
    Route::prefix('savings')->name('savings.')->group(function () {   

        // Admin-specific routes
        Route::middleware(['role:admin'])->group(function () {
            // Single entry management
            Route::post('/single-entry', [SavingController::class, 'storeSingleEntry'])->name('single.store');
            
            // Bulk upload
            Route::get('/bulk-upload', [SavingBulkController::class, 'showUploadForm'])->name('bulk.form');
            Route::post('/bulk-upload', [SavingBulkController::class, 'upload'])->name('bulk.upload');
            Route::get('/bulk-batches', [SavingBulkController::class, 'index'])->name('bulk.index');
            Route::get('/bulk-batches/{batch}', [SavingBulkController::class, 'show'])->name('bulk.show');
            
            // Download templates
            Route::get('/bulk-template', [SavingBulkController::class, 'downloadTemplate'])->name('bulk.template');
            
            // Approval management
            Route::put('/{saving}/approve', [SavingController::class, 'approve'])->name('approve');
            Route::put('/{saving}/reject', [SavingController::class, 'reject'])->name('reject');
            
            // Admin reports and management
            Route::get('/pending-approval', [SavingController::class, 'pendingApproval'])->name('pending');
            Route::get('/reports', [SavingController::class, 'reports'])->name('reports');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Withdrawal Requests Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [WithdrawalRequestController::class, 'index'])->name('index');
        Route::get('/{withdrawalRequest}', [WithdrawalRequestController::class, 'show'])->name('show');
        Route::patch('/{withdrawalRequest}/approve', [WithdrawalRequestController::class, 'approve'])->name('approve');
        Route::patch('/{withdrawalRequest}/reject', [WithdrawalRequestController::class, 'reject'])->name('reject');
    });
});