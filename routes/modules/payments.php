<?php

use App\Modules\Payments\Controllers\ComprobanteController;
use App\Modules\Payments\Controllers\PagoCheckoutController;
use App\Modules\Payments\Controllers\PagoController;
use App\Modules\Payments\Controllers\ReciboController;
use Illuminate\Support\Facades\Route;

// Módulo Payments (/api/payments).

// Webhooks de pasarela: públicos (los llama la pasarela, valida por firma).
Route::post('webhook/stripe', [PagoCheckoutController::class, 'webhookStripe'])->name('payments.webhook.stripe');
Route::post('webhook/paypal', [PagoCheckoutController::class, 'webhookPaypal'])->name('payments.webhook.paypal');

Route::middleware(['auth:api', 'password.changed', 'permission:payment.manage'])->group(function () {
    // El detalle (show) se define aparte para permitirlo también al dueño del pago.
    Route::apiResource('pagos', PagoController::class)->except('show');
    // Confirmación/rechazo manual (staff).
    Route::post('pagos/{pago}/confirmar', [PagoController::class, 'confirmar'])->name('pagos.confirmar');
    Route::post('pagos/{pago}/rechazar', [PagoController::class, 'rechazar'])->name('pagos.rechazar');
    // Revisión de comprobantes (staff).
    Route::get('pagos/{pago}/comprobantes', [ComprobanteController::class, 'index'])->name('pagos.comprobantes.index');
    Route::get('comprobantes/{comprobante}/descargar', [ComprobanteController::class, 'download'])->name('comprobantes.descargar');
});

// Pago propio: detalle, checkout y comprobante (cualquier autenticado; show valida dueño/staff).
Route::middleware(['auth:api', 'password.changed'])->group(function () {
    Route::get('pagos/{pago}', [PagoController::class, 'show'])->name('pagos.show');
    Route::post('pagos/{pago}/checkout', [PagoCheckoutController::class, 'checkout'])->name('pagos.checkout');
    Route::post('pagos/{pago}/comprobantes', [ComprobanteController::class, 'store'])->name('pagos.comprobantes.store');
    Route::get('pagos/{pago}/recibo', [ReciboController::class, 'download'])->name('pagos.recibo');
});
