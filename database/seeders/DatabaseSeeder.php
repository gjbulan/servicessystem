<?php

namespace Database\Seeders;

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
        $this->call(SaasFoundationSeeder::class);

        $user = User::updateOrCreate(
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
            $user->roles()->syncWithoutDetaching([
                $superAdminRole->id => ['branch_id' => null],
            ]);
        }
    }
}
