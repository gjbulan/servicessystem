<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyModule;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the authenticated SaaS foundation dashboard.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user()->loadMissing(['company.modules', 'roles']);
        $isPlatformAdmin = $user->company_id === null && $user->isSuperAdmin();

        $foundationStats = [
            [
                'label' => 'Companies',
                'value' => Company::count(),
                'description' => $isPlatformAdmin ? 'Total tenant companies' : 'Tenant records available',
            ],
            [
                'label' => 'Module records',
                'value' => CompanyModule::count(),
                'description' => 'Company module toggles',
            ],
            [
                'label' => 'Roles',
                'value' => Role::count(),
                'description' => 'RBAC role records',
            ],
            [
                'label' => 'Permissions',
                'value' => Permission::count(),
                'description' => 'RBAC permission records',
            ],
            [
                'label' => 'Middleware',
                'value' => '4 aliases',
                'description' => 'Company, role, permission, module checks',
            ],
        ];

        return view('dashboard', [
            'foundationStats' => $foundationStats,
            'isPlatformAdmin' => $isPlatformAdmin,
            'user' => $user,
        ]);
    }
}
