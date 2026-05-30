<?php

use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Authentication\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Módulo Authentication (/api/auth).

// Públicas.
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

// Protegidas (requieren token).
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');

    // Cambio de contraseña (disponible aunque deba cambiarla).
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');

    // CRUD de usuarios (requiere permiso y haber cambiado la contraseña inicial).
    Route::middleware(['password.changed', 'permission:user.manage'])
        ->apiResource('users', UserController::class);
});
