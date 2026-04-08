<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRol
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!empty($roles) && !in_array(auth()->user()->rol, $roles)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        if (!auth()->user()->activo) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Tu cuenta está desactivada.');
        }

        return $next($request);
    }
}