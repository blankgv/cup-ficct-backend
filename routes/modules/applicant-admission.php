<?php

use Illuminate\Support\Facades\Route;

// Módulo ApplicantAdmission (/api/applicant-admission).
Route::middleware(['auth:api', 'permission:applicant.manage'])->group(function () {
    // Route::post('/applicants', [ApplicantController::class, 'store']);
});
