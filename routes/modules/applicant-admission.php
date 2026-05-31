<?php

use App\Modules\ApplicantAdmission\Controllers\ConvocatoriaController;
use App\Modules\ApplicantAdmission\Controllers\PostulanteController;
use Illuminate\Support\Facades\Route;

// Módulo ApplicantAdmission (/api/applicant-admission).
Route::middleware(['auth:api', 'password.changed', 'permission:applicant.manage'])->group(function () {
    Route::apiResource('postulantes', PostulanteController::class);
    Route::apiResource('convocatorias', ConvocatoriaController::class);

    // Cupos por carrera en la convocatoria.
    Route::get('convocatorias/{convocatoria}/cupos', [ConvocatoriaController::class, 'cupos']);
    Route::put('convocatorias/{convocatoria}/cupos', [ConvocatoriaController::class, 'setCupos']);
    Route::delete('convocatorias/{convocatoria}/cupos/{carrera}', [ConvocatoriaController::class, 'removeCarrera']);
});
