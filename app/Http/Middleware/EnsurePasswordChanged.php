<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Bloquea acciones si el usuario aún debe cambiar su contraseña inicial.
class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            abort(403, 'Debe cambiar su contraseña antes de continuar.');
        }

        return $next($request);
    }
}
