<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Member\SavingGatewayController;
use App\Http\Controllers\Member\SavingController;

/*
|--------------------------------------------------------------------------
| Savings Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Member Savings Routes
    Route::prefix('savings')->name('savings.')->group(function () {
        

        // Member-specific routes
        Route::middleware(['role:member'])->group(function () {
            // Gateway payment
            Route::post('/gateway/initiate', [SavingGatewayController::class, 'initiate'])->name('gateway.initiate');
            Route::get('/gateway/verify/{transaction}', [SavingGatewayController::class, 'verify'])->name('gateway.verify');
            
            // Bank deposit
            Route::post('/deposit', [SavingController::class, 'storeDeposit'])->name('deposit.store');
            Route::post('/{saving}/proof', [SavingController::class, 'updateProof'])->name('deposit.proof');
        });

    });
});

