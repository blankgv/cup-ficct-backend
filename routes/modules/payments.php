<?php

use App\Modules\Payments\Controllers\PagoCheckoutController;
use App\Modules\Payments\Controllers\PagoController;
use Illuminate\Support\Facades\Route;

// Módulo Payments (/api/payments).

// Webhook de Stripe: público (lo llama Stripe, valida por firma).
Route::post('webhook/stripe', [PagoCheckoutController::class, 'webhookStripe'])->name('payments.webhook.stripe');

Route::middleware(['auth:api', 'password.changed', 'permission:payment.manage'])->group(function () {
    Route::apiResource('pagos', PagoController::class);
});

// Checkout: cualquier usuario autenticado (el postulante paga su propio pago).
Route::middleware(['auth:api', 'password.changed'])->group(function () {
    Route::post('pagos/{pago}/checkout', [PagoCheckoutController::class, 'checkout'])->name('pagos.checkout');
});
