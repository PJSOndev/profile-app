<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array(strtolower((string) $user->role), ['admin', 'super admin'], true)) {
            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
