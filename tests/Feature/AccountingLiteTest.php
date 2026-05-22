<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\JobOrder;
use App\Models\Role;
use App\Models\Sale;
use App\Models\TechnicianIncentive;
use App\Models\User;
use Database\Seeders\SaasFoundationSeeder;
use Tests\TestCase;

function makeAccountingLiteTenant(TestCase $testCase, string $suffix = 'primary', bool $enableAccounting = true): array
{
    $testCase->seed(SaasFoundationSeeder::class);

    $company = Company::create([
        'name' => "Accounting {$suffix} Company",
        'slug' => "accounting-{$suffix}-company",
        'status' => 'active',
    ]);

    if ($enableAccounting) {
        $company->enableModule('accounting');
    }

    $user = User::factory()->create([
        'company_id' => $company->id,
        'email' => "accounting-admin-{$suffix}@example.com",
        'status' => 'active',
    ]);
    $user->roles()->attach(Role::where('name', 'Company Admin')->firstOrFail()->id, ['branch_id' => null]);

    $branch = Branch::create([
        'company_id' => $company->id,
        'name' => "Accounting Branch {$suffix}",
        'status' => 'active',
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'name' => "Accounting Customer {$suffix}",
        'phone' => '0918'.fake()->unique()->numerify('######'),
        'status' => 'active',
    ]);

    return compact('branch', 'company', 'customer', 'user');
}

function createAccountingExpenseCategory(array $tenant, string $name = 'Utilities'): ExpenseCategory
{
    return ExpenseCategory::create([
        'company_id' => $tenant['company']->id,
        'name' => $name,
        'status' => 'active',
    ]);
}

function createAccountingSale(array $tenant, string $status, float $total, float $costPrice, float $quantity = 1, ?float $balanceDue = null): Sale
{
    $item = Item::create([
        'company_id' => $tenant['company']->id,
        'name' => 'Accounting Item '.fake()->unique()->numerify('###'),
        'status' => 'active',
    ]);

    $variant = ItemVariant::create([
        'company_id' => $tenant['company']->id,
        'item_id' => $item->id,
        'variant_name' => 'Default',
        'sku' => 'ACC-'.fake()->unique()->numerify('######'),
        'cost_price' => $costPrice,
        'selling_price' => $total / max($quantity, 1),
        'status' => 'active',
    ]);

    $balanceDue ??= $status === 'paid' ? 0 : $total;

    $sale = Sale::create([
        'company_id' => $tenant['company']->id,
        'branch_id' => $tenant['branch']->id,
        'customer_id' => $tenant['customer']->id,
        'sale_number' => 'ACC-SALE-'.fake()->unique()->numerify('######'),
        'status' => $status,
        'sale_date' => now()->toDateString(),
        'subtotal' => $total,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total' => $total,
        'amount_paid' => max($total - $balanceDue, 0),
        'balance_due' => $balanceDue,
        'created_by' => $tenant['user']->id,
    ]);

    $sale->items()->create([
        'company_id' => $tenant['company']->id,
        'item_variant_id' => $variant->id,
        'item_name_snapshot' => $item->name,
        'variant_name_snapshot' => $variant->variant_name,
        'sku_snapshot' => $variant->sku,
        'quantity' => $quantity,
        'unit_price' => $total / max($quantity, 1),
        'cost_price_snapshot' => $costPrice,
        'line_total' => $total,
    ]);

    return $sale;
}

function createAccountingPaidTechnicianIncentive(array $tenant, float $amount): TechnicianIncentive
{
    $technician = User::factory()->create([
        'company_id' => $tenant['company']->id,
        'email' => 'accounting-tech-'.fake()->unique()->numerify('####').'@example.com',
        'status' => 'active',
    ]);
    $technician->roles()->attach(Role::where('name', 'Technician')->firstOrFail()->id, ['branch_id' => null]);

    $jobOrder = JobOrder::create([
        'company_id' => $tenant['company']->id,
        'branch_id' => $tenant['branch']->id,
        'customer_id' => $tenant['customer']->id,
        'job_order_number' => 'ACC-JO-'.fake()->unique()->numerify('######'),
        'status' => 'completed',
        'customer_complaint' => 'Accounting test job',
        'created_by' => $tenant['user']->id,
    ]);

    return TechnicianIncentive::create([
        'company_id' => $tenant['company']->id,
        'branch_id' => $tenant['branch']->id,
        'job_order_id' => $jobOrder->id,
        'technician_id' => $technician->id,
        'service_name_snapshot' => 'Accounting service',
        'default_amount' => $amount,
        'final_amount' => $amount,
        'status' => 'paid',
        'approved_by' => $tenant['user']->id,
        'approved_at' => now(),
        'paid_at' => now(),
    ]);
}

test('company user can create expense category', function () {
    $tenant = makeAccountingLiteTenant($this, 'category');

    $this->actingAs($tenant['user'])
        ->post(route('expense-categories.store'), [
            'name' => 'Electricity',
            'description' => 'Power bills',
            'status' => 'active',
            'sort_order' => 10,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('expense_categories', [
        'company_id' => $tenant['company']->id,
        'name' => 'Electricity',
        'status' => 'active',
    ]);
});

test('company user can create expense', function () {
    $tenant = makeAccountingLiteTenant($this, 'expense');
    $category = createAccountingExpenseCategory($tenant, 'Rent');

    $this->actingAs($tenant['user'])
        ->post(route('expenses.store'), [
            'branch_id' => $tenant['branch']->id,
            'expense_category_id' => $category->id,
            'expense_date' => now()->toDateString(),
            'reference_number' => 'OR-100',
            'description' => 'Monthly rent',
            'amount' => 15000,
            'status' => 'recorded',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('expenses', [
        'company_id' => $tenant['company']->id,
        'branch_id' => $tenant['branch']->id,
        'expense_category_id' => $category->id,
        'description' => 'Monthly rent',
        'status' => 'recorded',
    ]);
});

test('expenses are tenant scoped', function () {
    $tenant = makeAccountingLiteTenant($this, 'tenant-expenses');
    $otherTenant = makeAccountingLiteTenant($this, 'other-expenses');

    $ownExpense = Expense::create([
        'company_id' => $tenant['company']->id,
        'branch_id' => $tenant['branch']->id,
        'expense_date' => now()->toDateString(),
        'description' => 'Visible expense',
        'amount' => 100,
        'status' => 'recorded',
        'created_by' => $tenant['user']->id,
    ]);
    $otherExpense = Expense::create([
        'company_id' => $otherTenant['company']->id,
        'branch_id' => $otherTenant['branch']->id,
        'expense_date' => now()->toDateString(),
        'description' => 'Hidden expense',
        'amount' => 999,
        'status' => 'recorded',
        'created_by' => $otherTenant['user']->id,
    ]);

    $this->actingAs($tenant['user'])
        ->get(route('expenses.index'))
        ->assertOk()
        ->assertSee($ownExpense->description)
        ->assertDontSee($otherExpense->description);

    $this->actingAs($tenant['user'])
        ->get(route('expenses.show', $otherExpense))
        ->assertNotFound();
});

test('financial summary uses paid sales only', function () {
    $tenant = makeAccountingLiteTenant($this, 'paid-only');
    createAccountingSale($tenant, 'paid', 1000, 300);
    createAccountingSale($tenant, 'unpaid', 2500, 700);

    $this->actingAs($tenant['user'])
        ->get(route('reports.financial-summary'))
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary) => $summary['revenue'] === 1000.0);
});

test('unpaid sales appear in outstanding but not revenue', function () {
    $tenant = makeAccountingLiteTenant($this, 'outstanding');
    createAccountingSale($tenant, 'paid', 800, 200);
    createAccountingSale($tenant, 'unpaid', 1500, 400);
    createAccountingSale($tenant, 'partial', 1200, 300, balanceDue: 450);

    $this->actingAs($tenant['user'])
        ->get(route('reports.financial-summary'))
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary) => $summary['revenue'] === 800.0
            && $summary['outstanding_balance'] === 1950.0
            && $summary['outstanding_sales_count'] === 2);
});

test('cogs calculated from sale item cost snapshot', function () {
    $tenant = makeAccountingLiteTenant($this, 'cogs');
    createAccountingSale($tenant, 'paid', 900, 125, 3);

    $this->actingAs($tenant['user'])
        ->get(route('reports.income-statement'))
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary) => $summary['cogs'] === 375.0
            && $summary['gross_profit'] === 525.0);
});

test('technician incentives paid included separately', function () {
    $tenant = makeAccountingLiteTenant($this, 'incentives');
    createAccountingSale($tenant, 'paid', 1000, 300);
    createAccountingPaidTechnicianIncentive($tenant, 75);

    $this->actingAs($tenant['user'])
        ->get(route('reports.financial-summary'))
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary) => $summary['technician_incentives_paid'] === 75.0
            && $summary['net_profit'] === 625.0);
});

test('accounting routes blocked if module disabled', function () {
    $tenant = makeAccountingLiteTenant($this, 'disabled', enableAccounting: false);

    $this->actingAs($tenant['user'])
        ->get(route('expenses.index'))
        ->assertForbidden();
});

test('tenant cannot see another company expenses or report data', function () {
    $tenant = makeAccountingLiteTenant($this, 'report-tenant-a');
    $otherTenant = makeAccountingLiteTenant($this, 'report-tenant-b');
    createAccountingSale($tenant, 'paid', 500, 100);
    createAccountingSale($otherTenant, 'paid', 9000, 100);
    $otherExpense = Expense::create([
        'company_id' => $otherTenant['company']->id,
        'branch_id' => $otherTenant['branch']->id,
        'expense_date' => now()->toDateString(),
        'description' => 'Other tenant expense',
        'amount' => 333,
        'status' => 'recorded',
        'created_by' => $otherTenant['user']->id,
    ]);

    $this->actingAs($tenant['user'])
        ->get(route('expenses.show', $otherExpense))
        ->assertNotFound();

    $this->actingAs($tenant['user'])
        ->get(route('reports.financial-summary'))
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary) => $summary['revenue'] === 500.0);
});
