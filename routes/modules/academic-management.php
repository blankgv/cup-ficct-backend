<?php

use Illuminate\Support\Facades\Route;

// Módulo AcademicManagement (/api/academic-management).
Route::middleware(['auth:api', 'password.changed', 'permission:academic.manage'])->group(function () {
    // Route::get('/courses', [CourseController::class, 'index']);
});
