<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_inventory_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->boolean('enable_item_variants')->default(true);
            $table->timestamps();

            $table->unique('company_id');
        });

        $now = now();
        $settings = DB::table('companies')
            ->select('id')
            ->orderBy('id')
            ->get()
            ->map(fn ($company) => [
                'company_id' => $company->id,
                'enable_item_variants' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($settings !== []) {
            DB::table('company_inventory_settings')->insert($settings);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_inventory_settings');
    }
};
