<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\SaasFoundationSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

function phase35Tenant(TestCase $testCase, string $suffix = 'primary'): array
{
    $testCase->seed(SaasFoundationSeeder::class);

    $company = Company::create([
        'name' => "Staff {$suffix} Company",
        'slug' => "staff-{$suffix}-company",
        'status' => 'active',
    ]);

    $branch = Branch::create([
        'company_id' => $company->id,
        'name' => "Main Branch {$suffix}",
        'status' => 'active',
    ]);

    $admin = User::factory()->create([
        'company_id' => $company->id,
        'email' => "admin-{$suffix}@example.com",
        'status' => 'active',
    ]);

    $companyAdminRole = Role::where('name', 'Company Admin')->firstOrFail();
    $admin->roles()->attach($companyAdminRole->id, ['branch_id' => null]);

    return compact('admin', 'branch', 'company');
}

function phase35SuperAdmin(TestCase $testCase): User
{
    $testCase->seed(SaasFoundationSeeder::class);

    $admin = User::factory()->create([
        'company_id' => null,
        'email' => 'phase35-super@example.com',
        'status' => 'active',
    ]);

    $role = Role::where('name', 'Super Admin')->firstOrFail();
    $admin->roles()->attach($role->id, ['branch_id' => null]);

    return $admin;
}

test('company admin can create staff with company and selected role', function () {
    $tenant = phase35Tenant($this);
    $role = Role::where('name', 'Branch Manager')->firstOrFail();

    $response = $this->actingAs($tenant['admin'])
        ->post(route('staff.store'), [
            'name' => 'New Staff',
            'email' => 'new-staff@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => 'active',
            'role_id' => $role->id,
            'branch_id' => $tenant['branch']->id,
        ]);

    $staff = User::where('email', 'new-staff@example.com')->firstOrFail();

    $response->assertRedirect(route('staff.show', $staff));
    expect($staff->company_id)->toBe($tenant['company']->id);
    expect(Hash::check('password', $staff->password))->toBeTrue();
    expect($staff->hasRole('Branch Manager'))->toBeTrue();
    expect((int) $staff->roles()->firstOrFail()->pivot->branch_id)->toBe($tenant['branch']->id);
});

test('company admin cannot create super admin staff', function () {
    $tenant = phase35Tenant($this, 'blocked');
    $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

    $this->actingAs($tenant['admin'])
        ->post(route('staff.store'), [
            'name' => 'Bad Staff',
            'email' => 'bad-staff@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => 'active',
            'role_id' => $superAdminRole->id,
            'branch_id' => null,
        ])
        ->assertSessionHasErrors('role_id');

    expect(User::where('email', 'bad-staff@example.com')->exists())->toBeFalse();
});

test('company admin cannot see other company staff', function () {
    $tenant = phase35Tenant($this, 'tenant');
    $otherTenant = phase35Tenant($this, 'other');
    $otherStaff = User::factory()->create([
        'company_id' => $otherTenant['company']->id,
        'email' => 'other-staff@example.com',
        'status' => 'active',
    ]);

    $this->actingAs($tenant['admin'])
        ->get(route('staff.index'))
        ->assertOk()
        ->assertDontSee('other-staff@example.com');

    $this->actingAs($tenant['admin'])
        ->get(route('staff.show', $otherStaff))
        ->assertNotFound();
});

test('company admin cannot delete themselves', function () {
    $tenant = phase35Tenant($this, 'self-delete');

    $this->actingAs($tenant['admin'])
        ->delete(route('staff.destroy', $tenant['admin']))
        ->assertForbidden();

    expect(User::whereKey($tenant['admin']->id)->exists())->toBeTrue();
});

test('super admin can view users across companies', function () {
    $superAdmin = phase35SuperAdmin($this);
    $tenant = phase35Tenant($this, 'visible-a');
    $otherTenant = phase35Tenant($this, 'visible-b');

    User::factory()->create([
        'company_id' => $tenant['company']->id,
        'email' => 'visible-a@example.com',
    ]);
    User::factory()->create([
        'company_id' => $otherTenant['company']->id,
        'email' => 'visible-b@example.com',
    ]);

    $this->actingAs($superAdmin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('visible-a@example.com')
        ->assertSee('visible-b@example.com');
});

test('super admin can create user for selected company', function () {
    $superAdmin = phase35SuperAdmin($this);
    $tenant = phase35Tenant($this, 'platform-create');
    $role = Role::where('name', 'Cashier')->firstOrFail();

    $response = $this->actingAs($superAdmin)
        ->post(route('admin.users.store'), [
            'company_id' => $tenant['company']->id,
            'name' => 'Platform Created User',
            'email' => 'platform-created@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => 'active',
            'role_id' => $role->id,
            'branch_id' => $tenant['branch']->id,
        ]);

    $user = User::where('email', 'platform-created@example.com')->firstOrFail();

    $response->assertRedirect(route('admin.users.show', $user));
    expect($user->company_id)->toBe($tenant['company']->id);
    expect($user->hasRole('Cashier'))->toBeTrue();
    expect((int) $user->roles()->firstOrFail()->pivot->branch_id)->toBe($tenant['branch']->id);
});

test('super admin cannot delete the last super admin', function () {
    $superAdmin = phase35SuperAdmin($this);

    $this->actingAs($superAdmin)
        ->delete(route('admin.users.destroy', $superAdmin))
        ->assertSessionHasErrors('user');

    expect(User::whereKey($superAdmin->id)->exists())->toBeTrue();
});

test('inactive user cannot login', function () {
    $user = User::factory()->create([
        'email' => 'inactive-login@example.com',
        'password' => Hash::make('password'),
        'status' => 'inactive',
    ]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
