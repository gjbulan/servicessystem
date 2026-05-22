<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('technician_incentives', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_order_service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_name_snapshot');
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->decimal('override_amount', 12, 2)->nullable();
            $table->decimal('final_amount', 12, 2)->default(0);
            $table->text('override_reason')->nullable();
            $table->string('status')->default('pending')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['job_order_id', 'job_order_service_id', 'technician_id'], 'technician_incentives_unique_service_technician');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'technician_id']);
            $table->index(['company_id', 'branch_id']);
            $table->index(['company_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technician_incentives');
    }
};
