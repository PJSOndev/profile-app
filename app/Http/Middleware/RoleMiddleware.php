<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        $normalizedRole = strtolower((string) $user->role);
        $allowedRoles = array_map('strtolower', $roles);

        if (! in_array($normalizedRole, $allowedRoles, true)) {
            abort(403, 'You are not allowed to access this page.');
        }

        return $next($request);
    }
}
