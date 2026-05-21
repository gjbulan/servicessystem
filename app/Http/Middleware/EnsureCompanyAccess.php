<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->status !== 'active') {
            abort(403, 'Your account is not active.');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $company = $user->company;

        if (! $company) {
            abort(403, 'A company assignment is required.');
        }

        if (! in_array($company->status, ['active', 'trial'], true)) {
            abort(403, 'Your company account is not active.');
        }

        $routeCompanyId = $this->routeCompanyId($request);

        if ($routeCompanyId !== null && (int) $user->company_id !== $routeCompanyId) {
            abort(403, 'You do not have access to this company.');
        }

        return $next($request);
    }

    private function routeCompanyId(Request $request): ?int
    {
        $routeCompany = $request->route('company');

        if ($routeCompany instanceof Company) {
            return $routeCompany->id;
        }

        if (is_numeric($routeCompany)) {
            return (int) $routeCompany;
        }

        if (is_string($routeCompany) && $routeCompany !== '') {
            return Company::where('slug', $routeCompany)->value('id');
        }

        return null;
    }
}
