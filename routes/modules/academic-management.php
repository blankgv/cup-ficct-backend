<?php

use Illuminate\Support\Facades\Route;

// Módulo AcademicManagement (/api/academic-management).
Route::middleware(['auth:api', 'permission:academic.manage'])->group(function () {
    // Route::get('/courses', [CourseController::class, 'index']);
});
