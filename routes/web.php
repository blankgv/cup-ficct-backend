<?php

use Illuminate\Support\Facades\Route;

// Backend API-only. La raíz solo apunta a la API.
Route::get('/', fn () => response()->json([
    'service' => config('app.name'),
    'api' => url('/api/ping'),
]));
