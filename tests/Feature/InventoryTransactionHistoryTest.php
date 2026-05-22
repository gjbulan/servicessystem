<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\SaasFoundationSeeder;

function makeInventoryHistoryTenant(string $suffix, string $userName): array
{
    $company = Company::create([
        'name' => "Inventory {$suffix} Company",
        'slug' => "inventory-{$suffix}-company",
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'company_id' => $company->id,
        'name' => $userName,
        'status' => 'active',
    ]);

    $role = Role::where('name', 'Inventory Staff')->firstOrFail();
    $user->roles()->attach($role->id, ['branch_id' => null]);

    $branch = Branch::create([
        'company_id' => $company->id,
        'name' => "Main Branch {$suffix}",
        'status' => 'active',
    ]);

    $item = Item::create([
        'company_id' => $company->id,
        'name' => "Engine Oil {$suffix}",
        'status' => 'active',
    ]);

    $variant = ItemVariant::create([
        'company_id' => $company->id,
        'item_id' => $item->id,
        'variant_name' => "10W40 1L {$suffix}",
        'sku' => "OIL-{$suffix}",
        'cost_price' => 100,
        'selling_price' => 150,
        'status' => 'active',
    ]);

    return compact('company', 'user', 'branch', 'item', 'variant');
}

test('stock in page shows recent tenant inventory transactions latest first', function () {
    $this->seed(SaasFoundationSeeder::class);

    $tenant = makeInventoryHistoryTenant('primary', 'Inventory User');
    $otherTenant = makeInventoryHistoryTenant('other', 'Other Inventory User');

    InventoryTransaction::create([
        'company_id' => $tenant['company']->id,
        'branch_id' => $tenant['branch']->id,
        'item_variant_id' => $tenant['variant']->id,
        'transaction_type' => 'initial_stock',
        'quantity' => 2,
        'previous_stock' => 0,
        'new_stock' => 2,
        'notes' => 'Older stock note',
        'created_by' => $tenant['user']->id,
    ]);

    $this->actingAs($tenant['user'])
        ->post(route('inventory.stock-in.store'), [
            'branch_id' => $tenant['branch']->id,
            'item_variant_id' => $tenant['variant']->id,
            'transaction_type' => 'stock_in',
            'quantity' => 5,
            'notes' => 'Fresh stock note',
        ])
        ->assertRedirect(route('inventory.stock-in.create'));

    InventoryTransaction::create([
        'company_id' => $otherTenant['company']->id,
        'branch_id' => $otherTenant['branch']->id,
        'item_variant_id' => $otherTenant['variant']->id,
        'transaction_type' => 'stock_in',
        'quantity' => 99,
        'previous_stock' => 0,
        'new_stock' => 99,
        'notes' => 'Other tenant secret note',
        'created_by' => $otherTenant['user']->id,
    ]);

    $this->actingAs($tenant['user'])
        ->get(route('inventory.stock-in.create'))
        ->assertOk()
        ->assertSee('Recent Inventory Transactions')
        ->assertSee('Main Branch primary')
        ->assertSee('Engine Oil primary')
        ->assertSee('10W40 1L primary')
        ->assertSee('OIL-primary')
        ->assertSee('Stock In')
        ->assertSee('5.00')
        ->assertSee('0.00')
        ->assertSee('Fresh stock note')
        ->assertSee('Inventory User')
        ->assertSeeInOrder(['Fresh stock note', 'Older stock note'])
        ->assertDontSee('Other tenant secret note')
        ->assertDontSee('Main Branch other');
});
