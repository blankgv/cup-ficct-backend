<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Permite el paso si el usuario tiene alguno de los permisos (separados por |).
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        $needed = explode('|', $permission);

        if (! $user || ! collect($needed)->contains(fn (string $p) => $user->hasPermission($p))) {
            abort(403, 'No tiene permiso para esta acción.');
        }

        return $next($request);
    }
}
