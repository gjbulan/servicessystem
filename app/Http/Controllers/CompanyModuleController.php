<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyModule;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyModuleController extends Controller
{
    /**
     * Show module toggle settings for the current company or all companies for Super Admin.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($this->canManageAllCompanies($user)) {
            $companies = Company::query()
                ->orderBy('name')
                ->get();

            $companies->each->ensureDefaultModules();
            $companies->load(['modules' => fn ($query) => $query->orderBy('module_name')]);
        } else {
            $company = $user->company;

            abort_unless($company, 403, 'A company assignment is required.');

            $company->ensureDefaultModules();
            $companies = collect([
                $company->load(['modules' => fn ($query) => $query->orderBy('module_name')]),
            ]);
        }

        return view('settings.modules', [
            'companies' => $companies,
            'moduleDefinitions' => CompanyModule::definitions(),
        ]);
    }

    /**
     * Update one company module toggle.
     */
    public function update(Request $request, CompanyModule $companyModule): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $this->canManageAllCompanies($user) || (int) $user->company_id === (int) $companyModule->company_id,
            403,
            'You do not have access to update this company module.',
        );

        $request->validate([
            'is_enabled' => ['required', 'boolean'],
        ]);

        $companyModule->update([
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        return back()->with('status', 'Module settings updated.');
    }

    private function canManageAllCompanies(User $user): bool
    {
        return $user->company_id === null && $user->isSuperAdmin();
    }
}
