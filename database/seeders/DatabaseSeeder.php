<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SaasFoundationSeeder::class,
        ]);

        $superAdmin = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'company_id' => null,
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'status' => 'active',
            ],
        );

        $superAdminRole = Role::query()
            ->whereNull('company_id')
            ->where('name', 'Super Admin')
            ->first();

        if ($superAdminRole) {
            $superAdmin->roles()->syncWithoutDetaching([
                $superAdminRole->id => ['branch_id' => null],
            ]);
        }

        $demoCompany = Company::withTrashed()->firstOrNew([
            'slug' => 'demo-motoshop',
        ]);

        $demoCompany->fill([
            'name' => 'Demo Motoshop',
            'email' => 'demo@example.com',
            'phone' => null,
            'address' => null,
            'status' => 'active',
        ]);
        $demoCompany->deleted_at = null;
        $demoCompany->save();

        $demoCompany->ensureDefaultModules();

        $demoAdmin = User::updateOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'company_id' => $demoCompany->id,
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
            ],
        );

        $companyAdminRole = Role::query()
            ->whereNull('company_id')
            ->where('name', 'Company Admin')
            ->first();

        if ($companyAdminRole) {
            $demoAdmin->roles()->syncWithoutDetaching([
                $companyAdminRole->id => ['branch_id' => null],
            ]);
        }

        $this->call(CompanyModuleSeeder::class);
    }
}
