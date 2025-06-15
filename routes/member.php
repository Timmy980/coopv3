<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Member\SavingGatewayController;
use App\Http\Controllers\Member\SavingController;
use App\Http\Controllers\Member\AccountController;
use App\Http\Controllers\Member\WithdrawalRequestController;

/*
|--------------------------------------------------------------------------
| Savings Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:member'])->group(function () {
    // Member Savings Routes
    Route::prefix('savings')->name('savings.')->group(function () {

        // Member-specific routes
        // Gateway payment
        Route::post('/gateway/initiate', [SavingGatewayController::class, 'initiate'])->name('gateway.initiate');
        Route::get('/gateway/verify/{transaction}', [SavingGatewayController::class, 'verify'])->name('gateway.verify');

        // Bank deposit
        Route::post('/deposit', [SavingController::class, 'storeDeposit'])->name('deposit.store');
        Route::post('/{saving}/proof', [SavingController::class, 'updateProof'])->name('deposit.proof');
    });
    Route::group([], function () {
        // Member Account Routes
        Route::get('/accounts', [AccountController::class, 'index'])->name('member.accounts.index');
        Route::get('/accounts/{account}', [AccountController::class, 'show'])->name('member.accounts.show');
    });

    Route::prefix('member')->name('member.')->group(function () {
        // Withdrawal Requests
        Route::resource('withdrawal-requests', WithdrawalRequestController::class)
            ->only(['index', 'create', 'store', 'show']);
    });
});




