<?php

use App\Models\Branch;
use App\Models\BranchItemVariantStock;
use App\Models\Company;
use App\Models\CompanyInventorySetting;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CompanyInventorySettingSeeder;
use Database\Seeders\SaasFoundationSeeder;
use Tests\TestCase;

function makeOptionalVariantModeUser(TestCase $testCase, string $suffix = 'primary'): array
{
    $testCase->seed(SaasFoundationSeeder::class);

    $company = Company::create([
        'name' => "Variant Mode {$suffix}",
        'slug' => "variant-mode-{$suffix}",
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'company_id' => $company->id,
        'status' => 'active',
    ]);

    $role = Role::where('name', 'Company Admin')->firstOrFail();
    $user->roles()->attach($role->id, ['branch_id' => null]);

    return compact('company', 'user');
}

test('existing companies receive default inventory settings through seeder', function () {
    $company = Company::create([
        'name' => 'Existing Inventory Company',
        'slug' => 'existing-inventory-company',
        'status' => 'active',
    ]);

    $company->inventorySetting()->delete();

    $this->seed(CompanyInventorySettingSeeder::class);

    $setting = $company->fresh()->inventorySetting;

    expect($setting)->toBeInstanceOf(CompanyInventorySetting::class);
    expect($setting->enable_item_variants)->toBeTrue();
    expect($company->fresh()->usesItemVariants())->toBeTrue();
});

test('new companies receive inventory setting automatically', function () {
    $company = Company::create([
        'name' => 'New Inventory Company',
        'slug' => 'new-inventory-company',
        'status' => 'active',
    ]);

    expect($company->fresh()->inventorySetting)->toBeInstanceOf(CompanyInventorySetting::class);
    expect($company->fresh()->usesItemVariants())->toBeTrue();
});

test('variant navigation is hidden when item variants are disabled', function () {
    ['company' => $company, 'user' => $user] = makeOptionalVariantModeUser($this, 'navigation');
    $company->inventorySetting()->update(['enable_item_variants' => false]);

    $this->actingAs($user)
        ->get(route('inventory.items.index'))
        ->assertOk()
        ->assertSee('Items')
        ->assertSee('Stock In')
        ->assertDontSee('Variants');
});

test('creating item while variants are disabled creates a default variant', function () {
    ['company' => $company, 'user' => $user] = makeOptionalVariantModeUser($this, 'create-item');
    $company->inventorySetting()->update(['enable_item_variants' => false]);

    $this->actingAs($user)
        ->post(route('inventory.items.store'), [
            'name' => 'Solar Panel',
            'description' => 'Simple inventory item',
            'item_category_id' => null,
            'item_brand_id' => null,
            'status' => 'active',
            'sku' => 'SOLAR-001',
            'barcode' => 'BAR-001',
            'cost_price' => 2500,
            'selling_price' => 3200,
            'unit_type' => 'pcs',
        ])
        ->assertRedirect();

    $item = Item::where('company_id', $company->id)->where('name', 'Solar Panel')->firstOrFail();
    $variant = $item->defaultVariant()->firstOrFail();

    expect($variant->variant_name)->toBe('Default');
    expect($variant->sku)->toBe('SOLAR-001');
    expect($variant->barcode)->toBe('BAR-001');
    expect((float) $variant->cost_price)->toBe(2500.0);
    expect((float) $variant->selling_price)->toBe(3200.0);
    expect($variant->unit_type)->toBe('pcs');
    expect($variant->status)->toBe('active');

    $this->actingAs($user)
        ->put(route('inventory.items.update', $item), [
            'name' => 'Solar Panel Updated',
            'description' => 'Updated simple inventory item',
            'item_category_id' => null,
            'item_brand_id' => null,
            'status' => 'active',
            'sku' => 'SOLAR-002',
            'barcode' => 'BAR-002',
            'cost_price' => 2600,
            'selling_price' => 3300,
            'unit_type' => 'set',
        ])
        ->assertRedirect(route('inventory.items.show', $item));

    $variant->refresh();

    expect($variant->variant_name)->toBe('Default');
    expect($variant->sku)->toBe('SOLAR-002');
    expect($variant->barcode)->toBe('BAR-002');
    expect((float) $variant->cost_price)->toBe(2600.0);
    expect((float) $variant->selling_price)->toBe(3300.0);
    expect($variant->unit_type)->toBe('set');
});

test('stock in works when item variants are disabled', function () {
    ['company' => $company, 'user' => $user] = makeOptionalVariantModeUser($this, 'stock-in');
    $company->inventorySetting()->update(['enable_item_variants' => false]);

    $branch = Branch::create([
        'company_id' => $company->id,
        'name' => 'Main Warehouse',
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->post(route('inventory.items.store'), [
            'name' => 'Battery',
            'description' => null,
            'item_category_id' => null,
            'item_brand_id' => null,
            'status' => 'active',
            'sku' => 'BAT-001',
            'barcode' => null,
            'cost_price' => 900,
            'selling_price' => 1250,
            'unit_type' => 'pcs',
        ])
        ->assertRedirect();

    $item = Item::where('company_id', $company->id)->where('name', 'Battery')->firstOrFail();
    $variant = $item->defaultVariant()->firstOrFail();

    $this->actingAs($user)
        ->post(route('inventory.stock-in.store'), [
            'branch_id' => $branch->id,
            'item_variant_id' => $variant->id,
            'transaction_type' => 'stock_in',
            'quantity' => 7,
            'notes' => 'Initial simple stock',
        ])
        ->assertRedirect(route('inventory.stock-in.create'));

    $stock = BranchItemVariantStock::where('company_id', $company->id)
        ->where('branch_id', $branch->id)
        ->where('item_variant_id', $variant->id)
        ->firstOrFail();

    expect((float) $stock->current_stock)->toBe(7.0);
    expect(InventoryTransaction::where('company_id', $company->id)->where('item_variant_id', $variant->id)->exists())->toBeTrue();

    $this->actingAs($user)
        ->get(route('inventory.stock-in.create'))
        ->assertOk()
        ->assertSee('Battery')
        ->assertSee('BAT-001')
        ->assertSee('Initial simple stock')
        ->assertDontSee('Battery - Default');
});
