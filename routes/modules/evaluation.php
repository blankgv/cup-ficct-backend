<?php

use Illuminate\Support\Facades\Route;

// Módulo Evaluation (/api/evaluation).
Route::middleware(['auth:api', 'password.changed', 'permission:grade.manage|attendance.manage'])->group(function () {
    // Route::get('/exams', [ExamController::class, 'index']);
});
