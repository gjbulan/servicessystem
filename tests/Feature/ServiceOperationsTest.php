<?php

use App\Models\AssetType;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\BranchItemVariantStock;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAsset;
use App\Models\CustomerAssetServiceHistory;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\JobOrder;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\SaasFoundationSeeder;
use Tests\TestCase;

function makeServiceOpsTenant(TestCase $testCase, string $suffix = 'primary'): array
{
    $testCase->seed(SaasFoundationSeeder::class);

    $company = Company::create([
        'name' => "Service {$suffix} Company",
        'slug' => "service-{$suffix}-company",
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'company_id' => $company->id,
        'email' => "service-admin-{$suffix}@example.com",
        'status' => 'active',
    ]);

    $role = Role::where('name', 'Company Admin')->firstOrFail();
    $user->roles()->attach($role->id, ['branch_id' => null]);

    $branch = Branch::create([
        'company_id' => $company->id,
        'name' => "Main Branch {$suffix}",
        'status' => 'active',
    ]);

    $assetType = AssetType::create([
        'company_id' => $company->id,
        'name' => "Motorcycle {$suffix}",
        'status' => 'active',
    ]);

    $serviceCategory = ServiceCategory::create([
        'company_id' => $company->id,
        'name' => "Maintenance {$suffix}",
        'status' => 'active',
    ]);

    $service = Service::create([
        'company_id' => $company->id,
        'service_category_id' => $serviceCategory->id,
        'name' => "Tune Up {$suffix}",
        'default_price' => 500,
        'status' => 'active',
    ]);

    return compact('assetType', 'branch', 'company', 'service', 'serviceCategory', 'user');
}

function createServiceOpsBooking(array $tenant, array $overrides = []): Booking
{
    $booking = Booking::create(array_replace([
        'company_id' => $tenant['company']->id,
        'branch_id' => $tenant['branch']->id,
        'booking_reference' => 'B20260522-'.fake()->unique()->numerify('####'),
        'customer_name' => 'Juan Rider',
        'phone' => '09170000001',
        'email' => 'juan@example.com',
        'asset_type_name' => $tenant['assetType']->name,
        'asset_details_json' => [
            'name' => 'Daily bike',
            'brand' => 'Yamaha',
            'model' => 'NMAX',
            'year' => '2022',
            'serial_number' => 'SER-001',
            'plate_number' => 'ABC-123',
            'color' => 'Blue',
            'notes' => 'Customer note',
        ],
        'preferred_datetime' => now()->addDay(),
        'issue_description' => 'Hard starting',
        'lead_source' => 'Facebook',
        'status' => 'pending',
    ], $overrides));

    $booking->services()->create([
        'service_id' => $tenant['service']->id,
        'service_name_snapshot' => $tenant['service']->name,
        'price_snapshot' => $tenant['service']->default_price,
    ]);

    return $booking;
}

function createServiceOpsJobOrder(array $tenant, array $overrides = []): JobOrder
{
    $customer = $overrides['customer'] ?? Customer::create([
        'company_id' => $tenant['company']->id,
        'name' => 'Job Customer',
        'phone' => '09170000002',
        'status' => 'active',
    ]);

    $asset = $overrides['asset'] ?? CustomerAsset::create([
        'company_id' => $tenant['company']->id,
        'customer_id' => $customer->id,
        'asset_type_id' => $tenant['assetType']->id,
        'name' => 'Service Asset',
        'status' => 'active',
    ]);

    $jobOrder = JobOrder::create([
        'company_id' => $tenant['company']->id,
        'branch_id' => $tenant['branch']->id,
        'customer_id' => $customer->id,
        'customer_asset_id' => $asset->id,
        'job_order_number' => 'JO20260522-'.fake()->unique()->numerify('####'),
        'status' => 'open',
        'customer_complaint' => 'Needs service',
        'created_by' => $tenant['user']->id,
    ]);

    $jobOrder->services()->create([
        'service_id' => $tenant['service']->id,
        'service_name_snapshot' => $tenant['service']->name,
        'price_snapshot' => $tenant['service']->default_price,
    ]);

    return $jobOrder;
}

test('public booking creates pending booking without customer record', function () {
    $tenant = makeServiceOpsTenant($this, 'public');

    $response = $this->post(route('public-bookings.store', ['company' => $tenant['company']->slug]), [
        'branch_id' => $tenant['branch']->id,
        'services' => [$tenant['service']->id],
        'customer_name' => 'Maria Customer',
        'phone' => '09175550000',
        'email' => 'maria@example.com',
        'asset_type_id' => $tenant['assetType']->id,
        'asset_name' => 'Family scooter',
        'brand' => 'Honda',
        'model' => 'Click',
        'year' => '2021',
        'serial_number' => 'SER-PUBLIC',
        'plate_number' => 'PUB-123',
        'color' => 'Red',
        'preferred_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
        'issue_description' => 'Oil leak',
        'lead_source' => 'Website',
        'notes' => 'Please call first',
    ]);

    $booking = Booking::where('company_id', $tenant['company']->id)->firstOrFail();

    $response->assertRedirect(route('public-bookings.create', ['company' => $tenant['company']->slug]));
    expect($booking->status)->toBe('pending');
    expect($booking->customer_id)->toBeNull();
    expect($booking->services()->count())->toBe(1);
    expect(Customer::where('company_id', $tenant['company']->id)->where('phone', '09175550000')->exists())->toBeFalse();
});

test('booking confirmation creates or updates customer', function () {
    $tenant = makeServiceOpsTenant($this, 'customer');
    $customer = Customer::create([
        'company_id' => $tenant['company']->id,
        'name' => 'Old Name',
        'phone' => '09170000001',
        'status' => 'active',
    ]);
    $booking = createServiceOpsBooking($tenant);

    $this->actingAs($tenant['user'])
        ->post(route('bookings.confirm', $booking))
        ->assertRedirect(route('bookings.show', $booking));

    $booking->refresh();
    $customer->refresh();

    expect($booking->customer_id)->toBe($customer->id);
    expect($customer->name)->toBe('Juan Rider');
    expect($customer->email)->toBe('juan@example.com');
});

test('booking confirmation creates customer asset and job order', function () {
    $tenant = makeServiceOpsTenant($this, 'confirm');
    $booking = createServiceOpsBooking($tenant);

    $this->actingAs($tenant['user'])
        ->post(route('bookings.confirm', $booking))
        ->assertRedirect(route('bookings.show', $booking));

    $booking->refresh();
    $asset = CustomerAsset::where('company_id', $tenant['company']->id)->firstOrFail();
    $jobOrder = JobOrder::where('company_id', $tenant['company']->id)->firstOrFail();

    expect($booking->status)->toBe('confirmed');
    expect($booking->customer_asset_id)->toBe($asset->id);
    expect($asset->plate_number)->toBe('ABC-123');
    expect($jobOrder->booking_id)->toBe($booking->id);
    expect($jobOrder->services()->count())->toBe(1);
});

test('multiple technicians can be assigned', function () {
    $tenant = makeServiceOpsTenant($this, 'techs');
    $jobOrder = createServiceOpsJobOrder($tenant);
    $technicianRole = Role::where('name', 'Technician')->firstOrFail();
    $firstTechnician = User::factory()->create(['company_id' => $tenant['company']->id, 'status' => 'active']);
    $secondTechnician = User::factory()->create(['company_id' => $tenant['company']->id, 'status' => 'active']);
    $firstTechnician->roles()->attach($technicianRole->id, ['branch_id' => null]);
    $secondTechnician->roles()->attach($technicianRole->id, ['branch_id' => null]);

    $this->actingAs($tenant['user'])
        ->post(route('job-orders.technicians.update', $jobOrder), [
            'technicians' => [
                ['technician_id' => $firstTechnician->id, 'role' => 'Lead', 'is_primary' => true],
                ['technician_id' => $secondTechnician->id, 'role' => 'Assistant', 'is_primary' => false],
            ],
        ])
        ->assertRedirect(route('job-orders.show', $jobOrder));

    expect($jobOrder->technicians()->count())->toBe(2);
    expect($jobOrder->technicians()->where('is_primary', true)->firstOrFail()->technician_id)->toBe($firstTechnician->id);
});

test('completing job order deducts stock once', function () {
    $tenant = makeServiceOpsTenant($this, 'stock');
    $jobOrder = createServiceOpsJobOrder($tenant);
    $item = Item::create(['company_id' => $tenant['company']->id, 'name' => 'Oil Filter', 'status' => 'active']);
    $variant = ItemVariant::create([
        'company_id' => $tenant['company']->id,
        'item_id' => $item->id,
        'variant_name' => 'Default',
        'sku' => 'FILTER-1',
        'cost_price' => 100,
        'selling_price' => 150,
        'status' => 'active',
    ]);

    BranchItemVariantStock::create([
        'company_id' => $tenant['company']->id,
        'branch_id' => $tenant['branch']->id,
        'item_variant_id' => $variant->id,
        'current_stock' => 5,
        'low_stock_threshold' => 0,
    ]);

    $jobOrder->items()->create([
        'item_variant_id' => $variant->id,
        'item_name_snapshot' => 'Oil Filter',
        'variant_name_snapshot' => 'Default',
        'sku_snapshot' => 'FILTER-1',
        'quantity' => 2,
        'cost_price_snapshot' => 100,
        'selling_price_snapshot' => 150,
    ]);

    $this->actingAs($tenant['user'])->post(route('job-orders.complete', $jobOrder))->assertRedirect();
    $this->actingAs($tenant['user'])->post(route('job-orders.complete', $jobOrder))->assertRedirect();

    $stock = BranchItemVariantStock::where('company_id', $tenant['company']->id)
        ->where('branch_id', $tenant['branch']->id)
        ->where('item_variant_id', $variant->id)
        ->firstOrFail();

    expect((float) $stock->current_stock)->toBe(3.0);
    expect(InventoryTransaction::where('company_id', $tenant['company']->id)
        ->where('transaction_type', 'job_order_usage')
        ->where('reference_id', $jobOrder->id)
        ->count())->toBe(1);
});

test('completing job order creates service history', function () {
    $tenant = makeServiceOpsTenant($this, 'history');
    $jobOrder = createServiceOpsJobOrder($tenant);

    $this->actingAs($tenant['user'])
        ->post(route('job-orders.complete', $jobOrder))
        ->assertRedirect(route('job-orders.show', $jobOrder));

    $history = CustomerAssetServiceHistory::where('company_id', $tenant['company']->id)->firstOrFail();
    $jobOrder->refresh();

    expect($jobOrder->status)->toBe('completed');
    expect($history->job_order_id)->toBe($jobOrder->id);
    expect($history->customer_asset_id)->toBe($jobOrder->customer_asset_id);
    expect($history->service_summary)->toContain('Tune Up history');
});

test('tenant cannot see another company bookings or job orders', function () {
    $tenant = makeServiceOpsTenant($this, 'tenant-a');
    $otherTenant = makeServiceOpsTenant($this, 'tenant-b');
    $otherBooking = createServiceOpsBooking($otherTenant);
    $otherJobOrder = createServiceOpsJobOrder($otherTenant);

    $this->actingAs($tenant['user'])
        ->get(route('bookings.show', $otherBooking))
        ->assertNotFound();

    $this->actingAs($tenant['user'])
        ->get(route('job-orders.show', $otherJobOrder))
        ->assertNotFound();
});
