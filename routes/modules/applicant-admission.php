<?php

use App\Modules\ApplicantAdmission\Controllers\ConvocatoriaController;
use App\Modules\ApplicantAdmission\Controllers\PostulacionController;
use App\Modules\ApplicantAdmission\Controllers\PostulanteController;
use Illuminate\Support\Facades\Route;

// Módulo ApplicantAdmission (/api/applicant-admission).
Route::middleware(['auth:api', 'password.changed', 'permission:applicant.manage'])->group(function () {
    Route::apiResource('postulantes', PostulanteController::class);

    // Postulaciones de un postulante (PK compuesta → resolución manual).
    Route::prefix('postulantes/{postulante}/postulaciones')->group(function () {
        Route::get('/', [PostulacionController::class, 'index']);
        Route::post('/', [PostulacionController::class, 'store']);
        Route::get('/{convocatoria}', [PostulacionController::class, 'show']);
        Route::delete('/{convocatoria}', [PostulacionController::class, 'destroy']);
    });
    Route::apiResource('convocatorias', ConvocatoriaController::class);

    // Cupos por carrera en la convocatoria.
    Route::get('convocatorias/{convocatoria}/cupos', [ConvocatoriaController::class, 'cupos']);
    Route::put('convocatorias/{convocatoria}/cupos', [ConvocatoriaController::class, 'setCupos']);
    Route::delete('convocatorias/{convocatoria}/cupos/{carrera}', [ConvocatoriaController::class, 'removeCarrera']);
});
