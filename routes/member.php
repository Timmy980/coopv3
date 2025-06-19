<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Member\SavingGatewayController;
use App\Http\Controllers\Member\SavingController;
use App\Http\Controllers\Member\AccountController;
use App\Http\Controllers\Member\WithdrawalRequestController;

Route::prefix('member')->name('member.')->middleware(['auth', 'verified', 'role:member'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Account Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('{account}', [AccountController::class, 'show'])->name('show');
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

        // Gateway Payment Routes
        Route::prefix('gateway')->name('gateway.')->group(function () {
            Route::post('initiate', [SavingGatewayController::class, 'initiate'])->name('initiate');
            Route::get('verify/{transaction}', [SavingGatewayController::class, 'verify'])->name('verify');
        });
        
        // Bank Deposit Routes
        Route::prefix('deposits')->name('deposits.')->group(function () {
            Route::post('/', [SavingController::class, 'storeDeposit'])->name('store');
            Route::patch('{saving}/proof', [SavingController::class, 'updateProof'])->name('update-proof');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Withdrawal Request Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [WithdrawalRequestController::class, 'index'])->name('index');
        Route::get('create', [WithdrawalRequestController::class, 'create'])->name('create');
        Route::post('/', [WithdrawalRequestController::class, 'store'])->name('store');
        Route::get('{withdrawalRequest}', [WithdrawalRequestController::class, 'show'])->name('show');
    });
});