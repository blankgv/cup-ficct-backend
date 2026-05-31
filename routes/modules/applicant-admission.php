<?php

use App\Modules\ApplicantAdmission\Controllers\PostulanteController;
use Illuminate\Support\Facades\Route;

// Módulo ApplicantAdmission (/api/applicant-admission).
Route::middleware(['auth:api', 'password.changed', 'permission:applicant.manage'])->group(function () {
    Route::apiResource('postulantes', PostulanteController::class);
});
