<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || $user->status !== 'active') {
            abort(403, 'Your account is not active.');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if ($roles === [] || ! $user->hasRole($roles)) {
            abort(403, 'You do not have the required role.');
        }

        return $next($request);
    }
}
