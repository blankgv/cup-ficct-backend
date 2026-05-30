<?php

use Illuminate\Support\Facades\Route;

// Módulo Payments (/api/payments).
Route::middleware('auth:api')->group(function () {
    // Route::post('/payments', [PaymentController::class, 'store']);
});
