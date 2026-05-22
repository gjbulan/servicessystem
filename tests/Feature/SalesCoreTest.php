<?php

use App\Models\Branch;
use App\Models\BranchItemVariantStock;
use App\Models\Company;
use App\Models\Customer;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\SaasFoundationSeeder;
use Tests\TestCase;

function makeSalesCoreTenant(TestCase $testCase, string $suffix = 'primary'): array
{
    $testCase->seed(SaasFoundationSeeder::class);

    $company = Company::create([
        'name' => "Sales {$suffix} Company",
        'slug' => "sales-{$suffix}-company",
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'company_id' => $company->id,
        'status' => 'active',
    ]);

    $role = Role::where('name', 'Company Admin')->firstOrFail();
    $user->roles()->attach($role->id, ['branch_id' => null]);

    $branch = Branch::create([
        'company_id' => $company->id,
        'name' => "Main Branch {$suffix}",
        'status' => 'active',
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'name' => "Customer {$suffix}",
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
        'sku' => "SKU-{$suffix}",
        'cost_price' => 60,
        'selling_price' => 100,
        'status' => 'active',
    ]);

    BranchItemVariantStock::create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'item_variant_id' => $variant->id,
        'current_stock' => 10,
        'low_stock_threshold' => 0,
    ]);

    return compact('company', 'user', 'branch', 'customer', 'item', 'variant');
}

function salesPayload(array $tenant, array $overrides = []): array
{
    return array_replace_recursive([
        'branch_id' => $tenant['branch']->id,
        'customer_id' => $tenant['customer']->id,
        'status' => 'unpaid',
        'sale_date' => now()->toDateString(),
        'discount_amount' => 0,
        'tax_amount' => 0,
        'notes' => 'Test sale',
        'items' => [
            [
                'item_variant_id' => $tenant['variant']->id,
                'quantity' => 2,
                'unit_price' => 100,
            ],
        ],
    ], $overrides);
}

test('company user can create sale', function () {
    $tenant = makeSalesCoreTenant($this);

    $response = $this->actingAs($tenant['user'])
        ->post(route('sales.store'), salesPayload($tenant));

    $sale = Sale::where('company_id', $tenant['company']->id)->firstOrFail();

    $response->assertRedirect(route('sales.show', $sale));
    expect($sale->branch_id)->toBe($tenant['branch']->id);
    expect($sale->customer_id)->toBe($tenant['customer']->id);
    expect($sale->status)->toBe('unpaid');
    expect((float) $sale->total)->toBe(200.0);
    expect((float) $sale->balance_due)->toBe(200.0);
});

test('sale item snapshots are saved', function () {
    $tenant = makeSalesCoreTenant($this, 'snapshot');

    $this->actingAs($tenant['user'])
        ->post(route('sales.store'), salesPayload($tenant, [
            'items' => [
                [
                    'item_variant_id' => $tenant['variant']->id,
                    'quantity' => 3,
                    'unit_price' => 120,
                ],
            ],
        ]))
        ->assertRedirect();

    $sale = Sale::where('company_id', $tenant['company']->id)->firstOrFail();
    $saleItem = $sale->items()->firstOrFail();

    expect($saleItem->item_name_snapshot)->toBe('Engine Oil snapshot');
    expect($saleItem->variant_name_snapshot)->toBe('10W40 1L snapshot');
    expect($saleItem->sku_snapshot)->toBe('SKU-snapshot');
    expect((float) $saleItem->quantity)->toBe(3.0);
    expect((float) $saleItem->unit_price)->toBe(120.0);
    expect((float) $saleItem->cost_price_snapshot)->toBe(60.0);
    expect((float) $saleItem->line_total)->toBe(360.0);
});

test('payment updates sale status', function () {
    $tenant = makeSalesCoreTenant($this, 'payment');

    $this->actingAs($tenant['user'])
        ->post(route('sales.store'), salesPayload($tenant))
        ->assertRedirect();

    $sale = Sale::where('company_id', $tenant['company']->id)->firstOrFail();

    $this->actingAs($tenant['user'])
        ->post(route('sales.payments.store', $sale), [
            'payment_method' => 'Cash',
            'reference_number' => null,
            'amount' => 50,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Partial payment',
        ])
        ->assertRedirect(route('sales.show', $sale));

    $sale->refresh();
    expect($sale->status)->toBe('partial');
    expect((float) $sale->amount_paid)->toBe(50.0);
    expect((float) $sale->balance_due)->toBe(150.0);

    $this->actingAs($tenant['user'])
        ->post(route('sales.payments.store', $sale), [
            'payment_method' => 'Cash',
            'reference_number' => null,
            'amount' => 150,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Final payment',
        ])
        ->assertRedirect(route('sales.show', $sale));

    $sale->refresh();
    expect($sale->status)->toBe('paid');
    expect((float) $sale->amount_paid)->toBe(200.0);
    expect((float) $sale->balance_due)->toBe(0.0);
});

test('paid sale deducts inventory once', function () {
    $tenant = makeSalesCoreTenant($this, 'deduct');

    $this->actingAs($tenant['user'])
        ->post(route('sales.store'), salesPayload($tenant))
        ->assertRedirect();

    $sale = Sale::where('company_id', $tenant['company']->id)->firstOrFail();

    $this->actingAs($tenant['user'])
        ->post(route('sales.payments.store', $sale), [
            'payment_method' => 'Cash',
            'reference_number' => 'PAY-1',
            'amount' => 200,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Paid in full',
        ])
        ->assertRedirect(route('sales.show', $sale));

    $stock = BranchItemVariantStock::where('company_id', $tenant['company']->id)
        ->where('branch_id', $tenant['branch']->id)
        ->where('item_variant_id', $tenant['variant']->id)
        ->firstOrFail();

    expect((float) $stock->current_stock)->toBe(8.0);

    $transaction = InventoryTransaction::where('company_id', $tenant['company']->id)
        ->where('transaction_type', 'sale')
        ->where('reference_type', 'Sale')
        ->where('reference_id', $sale->id)
        ->firstOrFail();

    expect((float) $transaction->quantity)->toBe(-2.0);
    expect((float) $transaction->previous_stock)->toBe(10.0);
    expect((float) $transaction->new_stock)->toBe(8.0);

    $this->actingAs($tenant['user'])
        ->post(route('sales.payments.store', $sale), [
            'payment_method' => 'Cash',
            'reference_number' => 'PAY-2',
            'amount' => 1,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Extra payment',
        ])
        ->assertSessionHasErrors('amount');

    $stock->refresh();
    expect((float) $stock->current_stock)->toBe(8.0);
    expect(InventoryTransaction::where('company_id', $tenant['company']->id)
        ->where('transaction_type', 'sale')
        ->where('reference_id', $sale->id)
        ->count())->toBe(1);
});

test('tenant cannot see another company sale', function () {
    $tenant = makeSalesCoreTenant($this, 'tenant');
    $otherTenant = makeSalesCoreTenant($this, 'other');

    $this->actingAs($otherTenant['user'])
        ->post(route('sales.store'), salesPayload($otherTenant))
        ->assertRedirect();

    $otherSale = Sale::where('company_id', $otherTenant['company']->id)->firstOrFail();

    $this->actingAs($tenant['user'])
        ->get(route('sales.show', $otherSale))
        ->assertNotFound();
});

test('variant mode off sales use the default variant', function () {
    $tenant = makeSalesCoreTenant($this, 'simple');
    $tenant['company']->inventorySetting()->update(['enable_item_variants' => false]);

    $this->actingAs($tenant['user'])
        ->post(route('inventory.items.store'), [
            'name' => 'Solar Battery',
            'description' => null,
            'item_category_id' => null,
            'item_brand_id' => null,
            'status' => 'active',
            'sku' => 'BAT-SIMPLE',
            'barcode' => null,
            'cost_price' => 900,
            'selling_price' => 1250,
            'unit_type' => 'pcs',
        ])
        ->assertRedirect();

    $item = Item::where('company_id', $tenant['company']->id)->where('name', 'Solar Battery')->firstOrFail();
    $variant = $item->defaultVariant()->firstOrFail();

    BranchItemVariantStock::create([
        'company_id' => $tenant['company']->id,
        'branch_id' => $tenant['branch']->id,
        'item_variant_id' => $variant->id,
        'current_stock' => 5,
        'low_stock_threshold' => 0,
    ]);

    $this->actingAs($tenant['user'])
        ->post(route('sales.store'), salesPayload($tenant, [
            'items' => [
                [
                    'item_variant_id' => $variant->id,
                    'quantity' => 1,
                    'unit_price' => 1250,
                ],
            ],
        ]))
        ->assertRedirect();

    $sale = Sale::where('company_id', $tenant['company']->id)
        ->latest()
        ->firstOrFail();
    $saleItem = $sale->items()->firstOrFail();

    expect($saleItem->item_name_snapshot)->toBe('Solar Battery');
    expect($saleItem->variant_name_snapshot)->toBeNull();
    expect($saleItem->sku_snapshot)->toBe('BAT-SIMPLE');

    $this->actingAs($tenant['user'])
        ->post(route('sales.payments.store', $sale), [
            'payment_method' => 'Cash',
            'reference_number' => null,
            'amount' => 1250,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Simple inventory payment',
        ])
        ->assertRedirect(route('sales.show', $sale));

    $stock = BranchItemVariantStock::where('company_id', $tenant['company']->id)
        ->where('branch_id', $tenant['branch']->id)
        ->where('item_variant_id', $variant->id)
        ->firstOrFail();

    expect((float) $stock->current_stock)->toBe(4.0);
});
