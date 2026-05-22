<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SaasFoundationSeeder;
use Tests\TestCase;

function makeSuperAdmin(TestCase $testCase): User
{
    $testCase->seed(SaasFoundationSeeder::class);

    $user = User::factory()->create([
        'company_id' => null,
        'status' => 'active',
    ]);

    $role = Role::where('name', 'Super Admin')->firstOrFail();
    $user->roles()->attach($role->id, ['branch_id' => null]);

    return $user;
}

test('super admins can view the company index', function () {
    $admin = makeSuperAdmin($this);

    $response = $this->actingAs($admin)->get(route('admin.companies.index'));

    $response->assertOk();
    $response->assertSee('Companies');
});

test('normal company users cannot access company management', function () {
    $this->seed(SaasFoundationSeeder::class);

    $company = Company::create([
        'name' => 'Tenant Company',
        'slug' => 'tenant-company',
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'company_id' => $company->id,
        'status' => 'active',
    ]);

    $role = Role::where('name', 'Technician')->firstOrFail();
    $user->roles()->attach($role->id, ['branch_id' => null]);

    $this->actingAs($user)
        ->get(route('admin.companies.index'))
        ->assertForbidden();
});

test('super admins can create a company with auto slug and default modules', function () {
    $admin = makeSuperAdmin($this);

    $response = $this->actingAs($admin)->post(route('admin.companies.store'), [
        'name' => 'Phase Company',
        'slug' => '',
        'email' => 'phase@example.com',
        'phone' => '09171234567',
        'address' => 'Metro Manila',
        'status' => 'active',
    ]);

    $company = Company::where('slug', 'phase-company')->firstOrFail();

    $response->assertRedirect(route('admin.companies.show', $company));
    expect($company->modules()->count())->toBe(12);
    expect($company->hasModule('inventory'))->toBeTrue();
    expect($company->hasModule('accounting'))->toBeFalse();
});

test('auto generated slugs avoid soft deleted company slugs', function () {
    $admin = makeSuperAdmin($this);

    $archived = Company::create([
        'name' => 'Archived Company',
        'slug' => 'archived-company',
        'status' => 'expired',
    ]);
    $archived->delete();

    $this->actingAs($admin)->post(route('admin.companies.store'), [
        'name' => 'Archived Company',
        'slug' => '',
        'email' => 'archived@example.com',
        'phone' => null,
        'address' => null,
        'status' => 'active',
    ])->assertRedirect();

    expect(Company::where('slug', 'archived-company-2')->exists())->toBeTrue();
});

test('super admins can update a company', function () {
    $admin = makeSuperAdmin($this);
    $company = Company::create([
        'name' => 'Old Name',
        'slug' => 'old-name',
        'email' => 'old@example.com',
        'status' => 'trial',
    ]);

    $response = $this->actingAs($admin)->put(route('admin.companies.update', $company), [
        'name' => 'New Name',
        'slug' => 'new-company-slug',
        'email' => 'new@example.com',
        'phone' => '09170000000',
        'address' => 'Updated address',
        'status' => 'suspended',
    ]);

    $response->assertRedirect(route('admin.companies.show', $company));

    $company->refresh();

    expect($company->name)->toBe('New Name');
    expect($company->slug)->toBe('new-company-slug');
    expect($company->status)->toBe('suspended');
});

test('super admins can soft delete a company', function () {
    $admin = makeSuperAdmin($this);
    $company = Company::create([
        'name' => 'Delete Company',
        'slug' => 'delete-company',
        'status' => 'active',
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.companies.destroy', $company));

    $response->assertRedirect(route('admin.companies.index'));
    $this->assertSoftDeleted('companies', [
        'id' => $company->id,
    ]);
});

test('super admins can assign an existing user to a company', function () {
    $admin = makeSuperAdmin($this);
    $company = Company::create([
        'name' => 'Assignment Company',
        'slug' => 'assignment-company',
        'status' => 'active',
    ]);
    $user = User::factory()->create([
        'company_id' => null,
        'status' => 'active',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.companies.users.assign', $company), [
        'user_id' => $user->id,
        'status' => 'inactive',
    ]);

    $response->assertRedirect();

    $user->refresh();

    expect($user->company_id)->toBe($company->id);
    expect($user->status)->toBe('inactive');
});

test('database seeder creates demo company admin without removing super admin', function () {
    $this->seed(DatabaseSeeder::class);

    $demoCompany = Company::where('slug', 'demo-motoshop')->firstOrFail();
    $demoAdmin = User::where('email', 'admin@demo.com')->firstOrFail();
    $superAdmin = User::where('email', 'test@example.com')->firstOrFail();

    expect($demoCompany->name)->toBe('Demo Motoshop');
    expect($demoCompany->modules()->count())->toBe(12);
    expect($demoAdmin->company_id)->toBe($demoCompany->id);
    expect($demoAdmin->hasRole('Company Admin'))->toBeTrue();
    expect($superAdmin->company_id)->toBeNull();
    expect($superAdmin->hasRole('Super Admin'))->toBeTrue();
});
