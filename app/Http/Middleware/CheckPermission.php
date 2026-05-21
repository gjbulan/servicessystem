<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || $user->status !== 'active') {
            abort(403, 'Your account is not active.');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if ($permissions === [] || ! $user->hasPermission($permissions)) {
            abort(403, 'You do not have the required permission.');
        }

        return $next($request);
    }
}
