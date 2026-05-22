<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanyInventorySettingSeeder extends Seeder
{
    /**
     * Seed default inventory settings for existing companies.
     */
    public function run(): void
    {
        Company::query()
            ->orderBy('id')
            ->chunkById(100, function ($companies): void {
                $companies->each->ensureDefaultInventorySetting();
            });
    }
}
