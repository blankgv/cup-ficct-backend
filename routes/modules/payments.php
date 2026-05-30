<?php

use Illuminate\Support\Facades\Route;

// Módulo Payments (/api/payments).
Route::middleware(['auth:api', 'permission:payment.manage'])->group(function () {
    // Route::post('/payments', [PaymentController::class, 'store']);
});
