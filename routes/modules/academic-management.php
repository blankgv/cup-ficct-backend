<?php

use App\Modules\AcademicManagement\Controllers\MateriaController;
use App\Modules\AcademicManagement\Controllers\ModuloController;
use Illuminate\Support\Facades\Route;

// Módulo AcademicManagement (/api/academic-management).
Route::middleware(['auth:api', 'password.changed', 'permission:academic.manage'])->group(function () {
    Route::apiResource('materias', MateriaController::class);
    Route::apiResource('modulos', ModuloController::class);
});
