<?php

use Illuminate\Support\Facades\Route;

// Rutas de la API (prefijo /api). Cada módulo en routes/modules/.

// Healthcheck.
Route::get('/ping', fn () => response()->json([
    'status' => 'ok',
    'service' => config('app.name'),
    'time' => now()->toIso8601String(),
]));

// Rutas por módulo.
Route::prefix('auth')->group(base_path('routes/modules/authentication.php'));
Route::prefix('academic-management')->group(base_path('routes/modules/academic-management.php'));
Route::prefix('applicant-admission')->group(base_path('routes/modules/applicant-admission.php'));
Route::prefix('payments')->group(base_path('routes/modules/payments.php'));
Route::prefix('evaluation')->group(base_path('routes/modules/evaluation.php'));
Route::prefix('reports')->group(base_path('routes/modules/reports.php'));
