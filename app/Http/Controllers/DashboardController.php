<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyModule;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
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

        $staffStats = null;
        $usersByCompany = collect();

        if ($isPlatformAdmin) {
            $foundationStats[] = [
                'label' => 'Platform users',
                'value' => User::count(),
                'description' => 'Total active platform user records',
            ];
            $foundationStats[] = [
                'label' => 'Active users',
                'value' => User::where('status', 'active')->count(),
                'description' => 'Users allowed to sign in',
            ];
            $foundationStats[] = [
                'label' => 'Inactive users',
                'value' => User::where('status', 'inactive')->count(),
                'description' => 'Users blocked from sign in',
            ];

            $usersByCompany = Company::query()
                ->withCount('users')
                ->orderBy('name')
                ->limit(8)
                ->get();
        } elseif ($user->company_id !== null) {
            $staffStats = [
                [
                    'label' => 'Total staff',
                    'value' => User::where('company_id', $user->company_id)->count(),
                    'description' => 'Users assigned to your company',
                ],
                [
                    'label' => 'Active staff',
                    'value' => User::where('company_id', $user->company_id)->where('status', 'active')->count(),
                    'description' => 'Staff allowed to sign in',
                ],
                [
                    'label' => 'Inactive staff',
                    'value' => User::where('company_id', $user->company_id)->where('status', 'inactive')->count(),
                    'description' => 'Staff blocked from sign in',
                ],
            ];
        }

        return view('dashboard', [
            'foundationStats' => $foundationStats,
            'isPlatformAdmin' => $isPlatformAdmin,
            'staffStats' => $staffStats,
            'user' => $user,
            'usersByCompany' => $usersByCompany,
        ]);
    }
}
