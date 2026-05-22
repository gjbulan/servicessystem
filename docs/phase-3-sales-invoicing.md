# Phase 3 Sales & Invoicing Core

## Purpose

Phase 3 adds the first sales and invoicing core workflow for tenant companies.

This phase builds sales, sale line items, sale payments, payment status updates, paid-sale stock deduction, and a print-friendly receipt/invoice page.

This phase does not build services, bookings, job orders, technician incentives, purchase orders, or accounting. It also does not create a separate invoices table yet.

## Database Tables Added

### `sales`

- `id`
- `company_id`
- `branch_id`
- `customer_id` nullable
- `sale_number`
- `status`: `draft`, `unpaid`, `partial`, `paid`, `void`
- `sale_date`
- `subtotal` decimal(12,2)
- `discount_amount` decimal(12,2), default `0`
- `tax_amount` decimal(12,2), default `0`
- `total` decimal(12,2)
- `amount_paid` decimal(12,2), default `0`
- `balance_due` decimal(12,2)
- `notes` nullable
- `created_by` nullable
- `created_at`, `updated_at`
- `deleted_at` for soft deletes

### `sale_items`

- `id`
- `company_id`
- `sale_id`
- `item_variant_id`
- `item_name_snapshot`
- `variant_name_snapshot` nullable
- `sku_snapshot` nullable
- `quantity` decimal(12,2)
- `unit_price` decimal(12,2)
- `cost_price_snapshot` decimal(12,2)
- `line_total` decimal(12,2)
- `created_at`, `updated_at`

### `sale_payments`

- `id`
- `company_id`
- `sale_id`
- `payment_method` nullable
- `reference_number` nullable
- `amount` decimal(12,2)
- `paid_at`
- `received_by` nullable
- `notes` nullable
- `created_at`, `updated_at`

## Models Added

- `App\Models\Sale`
- `App\Models\SaleItem`
- `App\Models\SalePayment`

## Relationships Added

- `Company` has many sales.
- `Branch` has many sales.
- `Customer` has many sales.
- `ItemVariant` has many sale items.
- `Sale` belongs to company, branch, customer, and creator user.
- `Sale` has many sale items.
- `Sale` has many sale payments.
- `SaleItem` belongs to company, sale, and item variant.
- `SalePayment` belongs to company, sale, and receiver user.

## Permission Added

- `manage_sales`

Assigned to:

- Super Admin
- Company Admin
- Branch Manager
- Cashier

## Routes Added

- `GET /sales`
- `GET /sales/create`
- `POST /sales`
- `GET /sales/{sale}`
- `GET /sales/{sale}/edit`
- `PUT/PATCH /sales/{sale}`
- `GET /sales/{sale}/payments`
- `POST /sales/{sale}/payments`
- `GET /sales/{sale}/print`

Route middleware:

- `auth`
- `verified`
- `company.access`
- `module:sales`
- `permission:manage_sales`

## Controller Added

- `App\Http\Controllers\Sales\SaleController`

## Views Added

- `resources/views/sales/index.blade.php`
- `resources/views/sales/create.blade.php`
- `resources/views/sales/edit.blade.php`
- `resources/views/sales/show.blade.php`
- `resources/views/sales/payments.blade.php`
- `resources/views/sales/print.blade.php`
- `resources/views/sales/_form.blade.php`

## Sales Workflow

- A sale belongs to one company and one branch.
- A sale may optionally belong to a customer.
- Sale numbers are generated per company with a date prefix.
- Sale items store snapshots of item name, variant name, SKU, unit price, and cost price.
- Sale item pricing can be edited before the sale has payments.
- Sales can be saved as `draft` or `unpaid`.
- Sales with payments, paid sales, partial sales, and void sales cannot be edited in this phase.
- The print page acts as a receipt/invoice view.

## Payment Workflow

- Payments are recorded through `/sales/{sale}/payments`.
- Payments cannot exceed the current balance due.
- When amount paid is less than total, the sale becomes `partial`.
- When amount paid is greater than or equal to total, the sale becomes `paid`.
- Paid sales have `balance_due = 0`.
- Void sales cannot receive payments.

## Inventory Deduction Workflow

Inventory is deducted only when a sale becomes `paid`.

When the sale becomes paid:

1. The system checks whether a `sale` inventory transaction already exists for the sale.
2. If not, it locks each branch stock row.
3. It verifies stock will not become negative.
4. It subtracts sold quantities from branch stock.
5. It creates `inventory_transactions` rows with:
   - `transaction_type = sale`
   - negative `quantity`
   - `reference_type = Sale`
   - `reference_id = sale id`

This prevents double deduction if payment processing or status handling is attempted again.

## Variant Mode Support

Sales always store `item_variant_id` internally.

When item variants are enabled:

- Sale item selectors show brand, item, variant, and SKU.
- `variant_name_snapshot` stores the selected variant name.

When item variants are disabled:

- Sale item selectors show item labels and SKU.
- The selected record is the item's hidden `Default` variant.
- `variant_name_snapshot` is stored as `null` to avoid exposing the default variant label.

## Tenant Safety

All sales, sale items, sale payments, stock rows, and inventory transaction queries are filtered by `company_id`.

Company users cannot view or update another company's sales.

## Navigation

The Sales link is shown only when:

- the route exists,
- the authenticated user belongs to a company,
- the company has the `sales` module enabled, and
- the user has `manage_sales`.

## Files Created

- `app/Http/Controllers/Sales/SaleController.php`
- `app/Models/Sale.php`
- `app/Models/SaleItem.php`
- `app/Models/SalePayment.php`
- `database/migrations/2026_05_22_000006_create_sales_tables.php`
- `resources/views/sales/_form.blade.php`
- `resources/views/sales/index.blade.php`
- `resources/views/sales/create.blade.php`
- `resources/views/sales/edit.blade.php`
- `resources/views/sales/show.blade.php`
- `resources/views/sales/payments.blade.php`
- `resources/views/sales/print.blade.php`
- `tests/Feature/SalesCoreTest.php`

## Files Changed

- `app/Models/Branch.php`
- `app/Models/Company.php`
- `app/Models/Customer.php`
- `app/Models/InventoryTransaction.php`
- `app/Models/ItemVariant.php`
- `app/Http/Controllers/Inventory/StockInController.php`
- `database/seeders/SaasFoundationSeeder.php`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/layouts/sidebar.blade.php`
- `routes/web.php`

## Verification

- `php artisan test --filter=SalesCoreTest`: 6 tests passed.
- `php artisan test`: 50 tests passed.
