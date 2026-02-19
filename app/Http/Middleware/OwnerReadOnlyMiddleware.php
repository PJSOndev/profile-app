<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerReadOnlyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && strtolower((string) $user->role) === 'owner'
            && ! in_array($request->method(), ['GET', 'HEAD'], true)
        ) {
            abort(403, 'Owner role is view-only.');
        }

        return $next($request);
    }
}
