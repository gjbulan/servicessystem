<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $user = $request->user();

        if (! $user || $user->status !== 'active') {
            abort(403, 'Your account is not active.');
        }

        if (! $user->canAccessModule($moduleKey)) {
            abort(403, 'This module is not enabled for your company.');
        }

        return $next($request);
    }
}
