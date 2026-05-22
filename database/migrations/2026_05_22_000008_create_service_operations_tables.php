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
        Schema::create('asset_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('customer_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('year')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('plate_number')->nullable();
            $table->string('color')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'customer_id']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('service_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('default_price', 12, 2)->default(0);
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->decimal('default_incentive_amount', 12, 2)->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('booking_reference');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('asset_type_name')->nullable();
            $table->json('asset_details_json')->nullable();
            $table->dateTime('preferred_datetime')->nullable();
            $table->text('issue_description')->nullable();
            $table->string('lead_source')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'booking_reference']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'branch_id']);
        });

        Schema::create('booking_services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('service_name_snapshot');
            $table->decimal('price_snapshot', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('job_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->string('job_order_number');
            $table->string('status')->default('open')->index();
            $table->text('customer_complaint')->nullable();
            $table->text('inspection_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('approval_status')->nullable();
            $table->text('approval_notes')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'job_order_number']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'branch_id']);
        });

        Schema::create('job_order_technicians', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['job_order_id', 'technician_id']);
        });

        Schema::create('job_order_services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_name_snapshot');
            $table->decimal('price_snapshot', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('job_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name_snapshot');
            $table->string('variant_name_snapshot')->nullable();
            $table->string('sku_snapshot')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->decimal('cost_price_snapshot', 12, 2)->default(0);
            $table->decimal('selling_price_snapshot', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_asset_service_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_order_id')->constrained()->cascadeOnDelete();
            $table->text('service_summary');
            $table->date('service_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('job_order_id');
            $table->index(['company_id', 'customer_id']);
            $table->index(['company_id', 'customer_asset_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_asset_service_histories');
        Schema::dropIfExists('job_order_items');
        Schema::dropIfExists('job_order_services');
        Schema::dropIfExists('job_order_technicians');
        Schema::dropIfExists('job_orders');
        Schema::dropIfExists('booking_services');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
        Schema::dropIfExists('customer_assets');
        Schema::dropIfExists('asset_types');
    }
};
