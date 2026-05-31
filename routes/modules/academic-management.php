<?php

use App\Modules\AcademicManagement\Controllers\AulaController;
use App\Modules\AcademicManagement\Controllers\GrupoController;
use App\Modules\AcademicManagement\Controllers\GrupoMateriaController;
use App\Modules\AcademicManagement\Controllers\HorarioController;
use App\Modules\AcademicManagement\Controllers\MateriaController;
use App\Modules\AcademicManagement\Controllers\ModuloController;
use Illuminate\Support\Facades\Route;

// Módulo AcademicManagement (/api/academic-management).
Route::middleware(['auth:api', 'password.changed', 'permission:academic.manage'])->group(function () {
    Route::apiResource('materias', MateriaController::class);
    Route::apiResource('modulos', ModuloController::class);
    Route::apiResource('grupos', GrupoController::class);

    // Materias de un grupo (muchos a muchos).
    Route::prefix('grupos/{grupo}/materias')->group(function () {
        Route::get('/', [GrupoMateriaController::class, 'index']);
        Route::put('/', [GrupoMateriaController::class, 'sync']);
        Route::post('/', [GrupoMateriaController::class, 'attach']);
        Route::delete('/{sigla}', [GrupoMateriaController::class, 'detach']);
    });

    // Horarios del grupo en una materia (PK compuesta de 3 → resolución manual).
    Route::prefix('grupos/{grupo}/materias/{sigla}/horarios')->group(function () {
        Route::get('/', [HorarioController::class, 'index']);
        Route::post('/', [HorarioController::class, 'store']);
        Route::get('/{numero}', [HorarioController::class, 'show']);
        Route::put('/{numero}', [HorarioController::class, 'update']);
        Route::delete('/{numero}', [HorarioController::class, 'destroy']);
    });

    // Aulas anidadas en el módulo (PK compuesta → resolución manual).
    Route::prefix('modulos/{modulo}/aulas')->group(function () {
        Route::get('/', [AulaController::class, 'index']);
        Route::post('/', [AulaController::class, 'store']);
        Route::get('/{numero}', [AulaController::class, 'show']);
        Route::put('/{numero}', [AulaController::class, 'update']);
        Route::delete('/{numero}', [AulaController::class, 'destroy']);
    });
});
