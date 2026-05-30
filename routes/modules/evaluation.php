<?php

use Illuminate\Support\Facades\Route;

// Módulo Evaluation (/api/evaluation).
Route::middleware(['auth:api', 'permission:grade.manage|attendance.manage'])->group(function () {
    // Route::get('/exams', [ExamController::class, 'index']);
});
