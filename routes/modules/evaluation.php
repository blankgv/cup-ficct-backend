<?php

use App\Modules\Evaluation\Controllers\AsistenciaController;
use App\Modules\Evaluation\Controllers\NotaController;
use Illuminate\Support\Facades\Route;

// Módulo Evaluation (/api/evaluation).

// Notas.
Route::middleware(['auth:api', 'password.changed', 'permission:grade.manage'])->group(function () {
    Route::post('notas', [NotaController::class, 'store']);
    Route::post('grupos/{grupo}/materias/{materia}/notas', [NotaController::class, 'storeBatch']);
    Route::get('postulantes/{postulante}/convocatorias/{convocatoria}/boletin', [NotaController::class, 'boletin']);
});

// Asistencia.
Route::middleware(['auth:api', 'password.changed', 'permission:attendance.manage'])->group(function () {
    Route::post('asistencias', [AsistenciaController::class, 'store']);
    Route::post('grupos/{grupo}/materias/{materia}/asistencias', [AsistenciaController::class, 'storeBatch']);
    Route::get('postulantes/{postulante}/convocatorias/{convocatoria}/asistencia', [AsistenciaController::class, 'reporte']);
});
