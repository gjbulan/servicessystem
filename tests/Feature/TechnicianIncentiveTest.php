<?php

use App\Models\AssetType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAsset;
use App\Models\JobOrder;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\TechnicianIncentive;
use App\Models\User;
use Database\Seeders\SaasFoundationSeeder;
use Tests\TestCase;

function makeTechnicianIncentiveTenant(TestCase $testCase, string $suffix = 'primary'): array
{
    $testCase->seed(SaasFoundationSeeder::class);

    $company = Company::create([
        'name' => "Incentive {$suffix} Company",
        'slug' => "incentive-{$suffix}-company",
        'status' => 'active',
    ]);

    $admin = User::factory()->create([
        'company_id' => $company->id,
        'email' => "incentive-admin-{$suffix}@example.com",
        'status' => 'active',
    ]);
    $admin->roles()->attach(Role::where('name', 'Company Admin')->firstOrFail()->id, ['branch_id' => null]);

    $branch = Branch::create([
        'company_id' => $company->id,
        'name' => "Incentive Branch {$suffix}",
        'status' => 'active',
    ]);

    $assetType = AssetType::create([
        'company_id' => $company->id,
        'name' => "Service Asset {$suffix}",
        'status' => 'active',
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'name' => "Incentive Customer {$suffix}",
        'phone' => '0917'.fake()->unique()->numerify('######'),
        'status' => 'active',
    ]);

    $asset = CustomerAsset::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'asset_type_id' => $assetType->id,
        'name' => "Customer Asset {$suffix}",
        'status' => 'active',
    ]);

    $serviceCategory = ServiceCategory::create([
        'company_id' => $company->id,
        'name' => "Service Category {$suffix}",
        'status' => 'active',
    ]);

    $service = Service::create([
        'company_id' => $company->id,
        'service_category_id' => $serviceCategory->id,
        'name' => "Tune Up {$suffix}",
        'default_price' => 500,
        'default_incentive_amount' => 125,
        'status' => 'active',
    ]);

    $technician = createTechnicianIncentiveUser($company, "technician-{$suffix}@example.com", 'Technician');
    $jobOrder = createTechnicianIncentiveJobOrder(compact('admin', 'asset', 'branch', 'company', 'customer', 'service'), $technician, $suffix);

    return compact('admin', 'asset', 'assetType', 'branch', 'company', 'customer', 'jobOrder', 'service', 'serviceCategory', 'technician');
}

function createTechnicianIncentiveUser(Company $company, string $email, string $roleName): User
{
    $user = User::factory()->create([
        'company_id' => $company->id,
        'email' => $email,
        'status' => 'active',
    ]);

    $user->roles()->attach(Role::where('name', $roleName)->firstOrFail()->id, ['branch_id' => null]);

    return $user;
}

function createTechnicianIncentiveJobOrder(array $tenant, User $technician, string $suffix = 'primary', ?Service $service = null): JobOrder
{
    $service ??= $tenant['service'];

    $jobOrder = JobOrder::create([
        'company_id' => $tenant['company']->id,
        'branch_id' => $tenant['branch']->id,
        'customer_id' => $tenant['customer']->id,
        'customer_asset_id' => $tenant['asset']->id,
        'job_order_number' => 'JO-INC-'.fake()->unique()->numerify('######'),
        'status' => 'open',
        'customer_complaint' => "Incentive job {$suffix}",
        'created_by' => $tenant['admin']->id,
    ]);

    $jobOrder->services()->create([
        'service_id' => $service->id,
        'service_name_snapshot' => $service->name,
        'price_snapshot' => $service->default_price,
    ]);

    $jobOrder->technicians()->create([
        'technician_id' => $technician->id,
        'role' => 'Technician',
        'is_primary' => true,
    ]);

    return $jobOrder;
}

test('completing job order generates incentives for assigned technicians', function () {
    $tenant = makeTechnicianIncentiveTenant($this, 'generate');
    $secondTechnician = createTechnicianIncentiveUser($tenant['company'], 'second-tech-generate@example.com', 'Technician');
    $tenant['jobOrder']->technicians()->create([
        'technician_id' => $secondTechnician->id,
        'role' => 'Assistant',
        'is_primary' => false,
    ]);

    $this->actingAs($tenant['admin'])
        ->post(route('job-orders.complete', $tenant['jobOrder']))
        ->assertRedirect(route('job-orders.show', $tenant['jobOrder']));

    expect(TechnicianIncentive::where('company_id', $tenant['company']->id)->count())->toBe(2);
    expect(TechnicianIncentive::where('technician_id', $tenant['technician']->id)->exists())->toBeTrue();
    expect(TechnicianIncentive::where('technician_id', $secondTechnician->id)->exists())->toBeTrue();
});

test('completing same job order twice does not duplicate incentives', function () {
    $tenant = makeTechnicianIncentiveTenant($this, 'duplicate');

    $this->actingAs($tenant['admin'])->post(route('job-orders.complete', $tenant['jobOrder']))->assertRedirect();
    $this->actingAs($tenant['admin'])->post(route('job-orders.complete', $tenant['jobOrder']))->assertRedirect();

    expect(TechnicianIncentive::where('job_order_id', $tenant['jobOrder']->id)->count())->toBe(1);
});

test('incentive uses service default incentive amount', function () {
    $tenant = makeTechnicianIncentiveTenant($this, 'default-amount');

    $this->actingAs($tenant['admin'])->post(route('job-orders.complete', $tenant['jobOrder']))->assertRedirect();

    $incentive = TechnicianIncentive::where('job_order_id', $tenant['jobOrder']->id)->firstOrFail();

    expect((float) $incentive->default_amount)->toBe(125.0);
    expect((float) $incentive->final_amount)->toBe(125.0);
    expect($incentive->service_name_snapshot)->toBe($tenant['service']->name);
});

test('override updates final amount', function () {
    $tenant = makeTechnicianIncentiveTenant($this, 'override');
    $manager = createTechnicianIncentiveUser($tenant['company'], 'branch-manager-override@example.com', 'Branch Manager');

    $this->actingAs($tenant['admin'])->post(route('job-orders.complete', $tenant['jobOrder']))->assertRedirect();
    $incentive = TechnicianIncentive::where('job_order_id', $tenant['jobOrder']->id)->firstOrFail();

    $this->actingAs($manager)
        ->put(route('technician-incentives.update', $incentive), [
            'override_amount' => 75.50,
            'override_reason' => 'Shared service effort',
        ])
        ->assertRedirect(route('technician-incentives.show', $incentive));

    $incentive->refresh();

    expect((float) $incentive->override_amount)->toBe(75.5);
    expect((float) $incentive->final_amount)->toBe(75.5);
    expect($incentive->override_reason)->toBe('Shared service effort');
});

test('paid incentive cannot be edited', function () {
    $tenant = makeTechnicianIncentiveTenant($this, 'paid-readonly');

    $this->actingAs($tenant['admin'])->post(route('job-orders.complete', $tenant['jobOrder']))->assertRedirect();
    $incentive = TechnicianIncentive::where('job_order_id', $tenant['jobOrder']->id)->firstOrFail();

    $this->actingAs($tenant['admin'])->post(route('technician-incentives.approve', $incentive))->assertRedirect();
    $this->actingAs($tenant['admin'])->post(route('technician-incentives.mark-paid', $incentive))->assertRedirect();

    $this->actingAs($tenant['admin'])
        ->put(route('technician-incentives.update', $incentive), [
            'override_amount' => 99,
            'override_reason' => 'Too late',
        ])
        ->assertForbidden();

    $incentive->refresh();

    expect($incentive->status)->toBe('paid');
    expect((float) $incentive->final_amount)->toBe(125.0);
});

test('technician sees only own incentives', function () {
    $tenant = makeTechnicianIncentiveTenant($this, 'own-view');
    $otherTechnician = createTechnicianIncentiveUser($tenant['company'], 'other-tech-own-view@example.com', 'Technician');
    $otherService = Service::create([
        'company_id' => $tenant['company']->id,
        'service_category_id' => $tenant['serviceCategory']->id,
        'name' => 'Hidden Brake Service',
        'default_price' => 800,
        'default_incentive_amount' => 200,
        'status' => 'active',
    ]);
    $otherJobOrder = createTechnicianIncentiveJobOrder($tenant, $otherTechnician, 'hidden', $otherService);

    $this->actingAs($tenant['admin'])->post(route('job-orders.complete', $tenant['jobOrder']))->assertRedirect();
    $this->actingAs($tenant['admin'])->post(route('job-orders.complete', $otherJobOrder))->assertRedirect();

    $otherIncentive = TechnicianIncentive::where('technician_id', $otherTechnician->id)->firstOrFail();

    $this->actingAs($tenant['technician'])
        ->get(route('technician-incentives.index'))
        ->assertOk()
        ->assertSee($tenant['service']->name)
        ->assertDontSee('Hidden Brake Service');

    $this->actingAs($tenant['technician'])
        ->get(route('technician-incentives.show', $otherIncentive))
        ->assertNotFound();
});

test('incentives are not generated if module disabled', function () {
    $tenant = makeTechnicianIncentiveTenant($this, 'disabled');
    $tenant['company']->disableModule('technician_incentives');

    $this->actingAs($tenant['admin'])->post(route('job-orders.complete', $tenant['jobOrder']))->assertRedirect();

    expect(TechnicianIncentive::where('job_order_id', $tenant['jobOrder']->id)->exists())->toBeFalse();
});

test('tenant cannot see another company incentives', function () {
    $tenant = makeTechnicianIncentiveTenant($this, 'tenant-a');
    $otherTenant = makeTechnicianIncentiveTenant($this, 'tenant-b');

    $this->actingAs($otherTenant['admin'])->post(route('job-orders.complete', $otherTenant['jobOrder']))->assertRedirect();
    $otherIncentive = TechnicianIncentive::where('company_id', $otherTenant['company']->id)->firstOrFail();

    $this->actingAs($tenant['admin'])
        ->get(route('technician-incentives.show', $otherIncentive))
        ->assertNotFound();
});
