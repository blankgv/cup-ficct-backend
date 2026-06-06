<?php

use App\Modules\ApplicantAdmission\Controllers\AsignacionCarreraController;
use App\Modules\ApplicantAdmission\Controllers\AsignacionGrupoController;
use App\Modules\ApplicantAdmission\Controllers\ConvocatoriaController;
use App\Modules\ApplicantAdmission\Controllers\PostulacionController;
use App\Modules\ApplicantAdmission\Controllers\PostulanteController;
use App\Modules\ApplicantAdmission\Controllers\VerificacionController;
use Illuminate\Support\Facades\Route;

// Módulo ApplicantAdmission (/api/applicant-admission).
Route::middleware(['auth:api', 'password.changed', 'permission:applicant.manage'])->group(function () {
    // Carga masiva (antes del apiResource para que /lote no choque con /{postulante}).
    Route::post('postulantes/lote', [PostulanteController::class, 'importLote']);
    Route::apiResource('postulantes', PostulanteController::class);

    // Título de bachiller (R2).
    Route::post('postulantes/{postulante}/titulo', [PostulanteController::class, 'uploadTitulo']);
    Route::get('postulantes/{postulante}/titulo', [PostulanteController::class, 'downloadTitulo']);

    // Postulaciones de un postulante (PK compuesta → resolución manual).
    Route::prefix('postulantes/{postulante}/postulaciones')->group(function () {
        Route::get('/', [PostulacionController::class, 'index']);
        Route::post('/', [PostulacionController::class, 'store']);
        Route::get('/{convocatoria}', [PostulacionController::class, 'show']);
        Route::put('/{convocatoria}/turno', [PostulacionController::class, 'setTurno']);
        Route::delete('/{convocatoria}', [PostulacionController::class, 'destroy']);
    });
    Route::apiResource('convocatorias', ConvocatoriaController::class);

    // Cupos por carrera en la convocatoria.
    Route::get('convocatorias/{convocatoria}/cupos', [ConvocatoriaController::class, 'cupos']);
    Route::put('convocatorias/{convocatoria}/cupos', [ConvocatoriaController::class, 'setCupos']);
    Route::delete('convocatorias/{convocatoria}/cupos/{carrera}', [ConvocatoriaController::class, 'removeCarrera']);
});

// Generación automática de grupos de la convocatoria (permiso aparte: applicant.assign).
Route::middleware(['auth:api', 'password.changed', 'permission:applicant.assign'])
    ->group(function () {
        Route::post('convocatorias/{convocatoria}/generar-grupos', [AsignacionGrupoController::class, 'generar']);
        Route::post('convocatorias/{convocatoria}/asignar-carreras', [AsignacionCarreraController::class, 'generar']);
    });

// Verificación de requisitos (permiso aparte: applicant.verify).
Route::middleware(['auth:api', 'password.changed', 'permission:applicant.verify'])
    ->prefix('postulantes/{postulante}/postulaciones/{convocatoria}')
    ->group(function () {
        Route::put('/verificar', [VerificacionController::class, 'verificar']);
        Route::put('/rechazar', [VerificacionController::class, 'rechazar']);
    });
