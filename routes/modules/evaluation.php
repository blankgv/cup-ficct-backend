<?php

use App\Modules\Evaluation\Controllers\NotaController;
use Illuminate\Support\Facades\Route;

// Módulo Evaluation (/api/evaluation).
Route::middleware(['auth:api', 'password.changed', 'permission:grade.manage'])->group(function () {
    // Notas.
    Route::post('notas', [NotaController::class, 'store']);
    Route::post('grupos/{grupo}/materias/{materia}/notas', [NotaController::class, 'storeBatch']);
    Route::get('postulantes/{postulante}/convocatorias/{convocatoria}/boletin', [NotaController::class, 'boletin']);
});
