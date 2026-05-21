<?php

namespace App\Http\Controllers;

use App\Models\Company;
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
        $user = $request->user()->loadMissing(['company', 'roles']);

        $foundationStats = [
            [
                'label' => 'Companies',
                'value' => Company::count(),
                'description' => 'Tenant records available',
            ],
            [
                'label' => 'System roles',
                'value' => Role::system()->count().' / 6',
                'description' => 'Default role catalog',
            ],
            [
                'label' => 'Permissions',
                'value' => Permission::count().' / 11',
                'description' => 'Default permission catalog',
            ],
            [
                'label' => 'Middleware',
                'value' => '3 aliases',
                'description' => 'Company, role, permission checks',
            ],
        ];

        return view('dashboard', [
            'foundationStats' => $foundationStats,
            'user' => $user,
        ]);
    }
}
