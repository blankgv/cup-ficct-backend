<?php

use Illuminate\Support\Facades\Route;

// Módulo Evaluation (/api/evaluation).
Route::middleware('auth:api')->group(function () {
    // Route::get('/exams', [ExamController::class, 'index']);
});
