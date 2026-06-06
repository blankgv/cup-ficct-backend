<?php

use App\Modules\Payments\Controllers\PagoController;
use Illuminate\Support\Facades\Route;

// Módulo Payments (/api/payments).
Route::middleware(['auth:api', 'password.changed', 'permission:payment.manage'])->group(function () {
    Route::apiResource('pagos', PagoController::class);
});
