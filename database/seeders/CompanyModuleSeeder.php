<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanyModuleSeeder extends Seeder
{
    /**
     * Seed default module records for existing companies.
     */
    public function run(): void
    {
        Company::query()
            ->orderBy('id')
            ->chunkById(100, function ($companies): void {
                $companies->each->ensureDefaultModules();
            });
    }
}
