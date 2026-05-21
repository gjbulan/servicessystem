<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\SaasFoundationSeeder;
use Illuminate\Support\Facades\Route;

test('companies receive default module records', function () {
    $company = Company::create([
        'name' => 'Demo Company',
        'slug' => 'demo-company',
        'status' => 'active',
    ]);

    $company->load('modules');

    expect($company->modules)->toHaveCount(12);
    expect($company->hasModule('inventory'))->toBeTrue();
    expect($company->hasModule('accounting'))->toBeFalse();
});

test('company admins can view module settings', function () {
    $this->seed(SaasFoundationSeeder::class);

    $company = Company::create([
        'name' => 'Settings Company',
        'slug' => 'settings-company',
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'company_id' => $company->id,
        'status' => 'active',
    ]);

    $role = Role::where('name', 'Company Admin')->firstOrFail();
    $user->roles()->attach($role->id, ['branch_id' => null]);

    $response = $this->actingAs($user)->get(route('settings.modules.index'));

    $response->assertOk();
    $response->assertSee('Company Modules');
    $response->assertSee('Inventory');
});

test('company admins can update a company module toggle', function () {
    $this->seed(SaasFoundationSeeder::class);

    $company = Company::create([
        'name' => 'Toggle Company',
        'slug' => 'toggle-company',
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'company_id' => $company->id,
        'status' => 'active',
    ]);

    $role = Role::where('name', 'Company Admin')->firstOrFail();
    $user->roles()->attach($role->id, ['branch_id' => null]);

    $module = $company->modules()->where('module_key', 'inventory')->firstOrFail();

    $response = $this->actingAs($user)->patch(route('settings.modules.update', $module), [
        'is_enabled' => '0',
    ]);

    $response->assertRedirect();
    expect($module->refresh()->is_enabled)->toBeFalse();
});

test('module middleware blocks disabled company modules', function () {
    Route::middleware(['web', 'auth', 'module:inventory'])
        ->get('/test-inventory-module', fn () => 'Inventory enabled');

    $company = Company::create([
        'name' => 'Middleware Company',
        'slug' => 'middleware-company',
        'status' => 'active',
    ]);

    $company->disableModule('inventory');

    $user = User::factory()->create([
        'company_id' => $company->id,
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->get('/test-inventory-module')
        ->assertForbidden()
        ->assertSee('This module is not enabled for your company.');
});

test('super admins without a company bypass module checks', function () {
    $this->seed(SaasFoundationSeeder::class);

    Route::middleware(['web', 'auth', 'module:bookings'])
        ->get('/test-super-admin-module', fn () => 'Module bypassed');

    $user = User::factory()->create([
        'company_id' => null,
        'status' => 'active',
    ]);

    $role = Role::where('name', 'Super Admin')->firstOrFail();
    $user->roles()->attach($role->id, ['branch_id' => null]);

    $this->actingAs($user)
        ->get('/test-super-admin-module')
        ->assertOk()
        ->assertSee('Module bypassed');
});
