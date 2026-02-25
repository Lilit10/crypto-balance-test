<?php

use App\Http\Controllers\CryptoBalanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('balance')->group(function () {
    Route::post('credit', [CryptoBalanceController::class, 'credit']);
    Route::post('debit', [CryptoBalanceController::class, 'debit']);
    Route::get('{userId}/{currency}', [CryptoBalanceController::class, 'show']);
});
