<?php

use Illuminate\Support\Facades\Route;

// Módulo Reports (/api/reports).
Route::middleware('auth:api')->group(function () {
    // Route::get('/summary', [ReportController::class, 'summary']);
});
