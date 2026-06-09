<?php

use App\Modules\Reports\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Módulo Reports (/api/reports). Consulta JSON con report.view; export (excel/pdf) exige report.export.
Route::middleware(['auth:api', 'password.changed', 'permission:report.view'])->group(function () {
    Route::get('estudiantes-por-grupo', [ReportController::class, 'estudiantesPorGrupo']);
    Route::get('convocatorias/{convocatoria}/postulantes', [ReportController::class, 'postulantes']);
    Route::get('convocatorias/{convocatoria}/recaudacion', [ReportController::class, 'recaudacion']);
    Route::get('convocatorias/{convocatoria}/resultados', [ReportController::class, 'resultados']);
    Route::get('convocatorias/{convocatoria}/asignacion-carreras', [ReportController::class, 'asignacionCarreras']);
    Route::get('convocatorias/{convocatoria}/admitidos', [ReportController::class, 'admitidos']);

    // Reporte por voz (texto transcrito → IA elige reporte + filtros).
    Route::post('voz', [ReportController::class, 'voz']);
});
