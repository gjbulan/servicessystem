<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyInventorySetting;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InventorySettingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($this->canManageAllCompanies($user)) {
            $companies = Company::query()
                ->orderBy('name')
                ->get();

            $companies->each->ensureDefaultInventorySetting();
            $companies->load('inventorySetting');
        } else {
            $company = $user->company;

            abort_unless($company, 403, 'A company assignment is required.');

            $company->ensureDefaultInventorySetting();
            $companies = collect([$company->load('inventorySetting')]);
        }

        return view('settings.inventory', [
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, CompanyInventorySetting $inventorySetting): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $this->canManageAllCompanies($user) || (int) $user->company_id === (int) $inventorySetting->company_id,
            403,
            'You do not have access to update this inventory setting.',
        );

        $request->validate([
            'enable_item_variants' => ['required', 'boolean'],
        ]);

        $inventorySetting->update([
            'enable_item_variants' => $request->boolean('enable_item_variants'),
        ]);

        return back()->with('status', 'Inventory settings updated.');
    }

    private function canManageAllCompanies(User $user): bool
    {
        return $user->company_id === null && $user->isSuperAdmin();
    }
}
