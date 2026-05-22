# Phase 2 Business Core

## Purpose

Phase 2 adds universal tenant business records that can support motoshops, inventory companies, retail stores, and hybrid service businesses.

This phase builds branches, customers, and inventory foundation only. Sales, POS, invoices, services, bookings, job orders, technician incentives, purchase orders, and accounting remain out of scope.

## Modules Added

- Branches
- Customers
- Item categories
- Item brands
- Items
- Item variants
- Branch stock
- Inventory transactions
- Stock-in transaction entry
- Stock-in recent transaction history

## Database Tables Added

- `branches`
- `customers`
- `item_categories`
- `item_brands`
- `items`
- `item_variants`
- `branch_item_variant_stocks`
- `inventory_transactions`

## Models Added

- `App\Models\Branch`
- `App\Models\Customer`
- `App\Models\ItemCategory`
- `App\Models\ItemBrand`
- `App\Models\Item`
- `App\Models\ItemVariant`
- `App\Models\BranchItemVariantStock`
- `App\Models\InventoryTransaction`

## Relationships Added

- `Company` has many branches.
- `Company` has many customers.
- `Company` has many item categories.
- `Company` has many item brands.
- `Company` has many items.
- `Company` has many item variants.
- `Branch` belongs to company.
- `Branch` has many branch item variant stocks.
- `Branch` has many inventory transactions.
- `Customer` belongs to company.
- `ItemCategory` belongs to company and has many items.
- `ItemBrand` belongs to company and has many items.
- `Item` belongs to company, category, and brand.
- `Item` has many variants.
- `ItemVariant` belongs to company and item.
- `ItemVariant` has many branch stock records.
- `ItemVariant` has many inventory transactions.
- `BranchItemVariantStock` belongs to company, branch, and item variant.
- `InventoryTransaction` belongs to company, branch, item variant, and creator user.

## Controllers Added

- `App\Http\Controllers\BranchController`
- `App\Http\Controllers\CustomerController`
- `App\Http\Controllers\Inventory\ItemCategoryController`
- `App\Http\Controllers\Inventory\ItemBrandController`
- `App\Http\Controllers\Inventory\ItemController`
- `App\Http\Controllers\Inventory\ItemVariantController`
- `App\Http\Controllers\Inventory\StockInController`

The shared `App\Http\Controllers\Concerns\ResolvesTenantCompany` concern resolves the authenticated user's company and keeps operational records tenant-scoped.

## Routes Added

Branch routes:

- `GET /branches`
- `GET /branches/create`
- `POST /branches`
- `GET /branches/{branch}`
- `GET /branches/{branch}/edit`
- `PUT/PATCH /branches/{branch}`
- `DELETE /branches/{branch}`

Customer routes:

- `GET /customers`
- `GET /customers/create`
- `POST /customers`
- `GET /customers/{customer}`
- `GET /customers/{customer}/edit`
- `PUT/PATCH /customers/{customer}`
- `DELETE /customers/{customer}`

Inventory routes:

- `GET /inventory/categories`
- `GET /inventory/brands`
- `GET /inventory/items`
- `GET /inventory/variants`
- `GET /inventory/stock-in`
- `POST /inventory/stock-in`

Resource create, show, edit, update, and delete routes were added for categories, brands, items, and variants.

## Middleware Rules

- Branches use `auth`, `verified`, `company.access`, and `permission:manage_branches`.
- Customers use `auth`, `verified`, `company.access`, `module:customers`, and `permission:manage_customers`.
- Inventory uses `auth`, `verified`, `company.access`, `module:inventory`, and `permission:manage_inventory`.

## Tenant Safety

Every Phase 2 controller resolves the authenticated user's company and filters records by `company_id`.

Normal company users can only access records where `company_id` matches their own company. Super Admin users with `company_id` as `null` cannot use tenant operational screens directly; they should manage companies through `/admin/companies` or sign in as a company-assigned user until impersonation exists.

## Inventory Architecture

Items are parent product records. Inventory quantities are not tracked directly on `items`.

Variants are the actual inventory units. Examples:

- Motul Engine Oil -> 10W40 1L
- Michelin Tire -> 70/90-14

Stock is tracked per branch and variant in `branch_item_variant_stocks`.

## Stock Transaction Flow

The stock-in screen records inventory movements through `/inventory/stock-in`.

When a stock transaction is submitted:

1. The selected branch must belong to the authenticated user's company.
2. The selected item variant must belong to the authenticated user's company.
3. The branch stock row is locked or created.
4. Previous stock and new stock are calculated.
5. An `inventory_transactions` record is created.
6. The branch stock row is updated.

`damage` subtracts stock. `initial_stock`, `stock_in`, `manual_adjustment`, and `return` currently add stock. Transactions that would make stock negative are blocked.

The stock-in page now also shows the latest 25 inventory transactions for the authenticated user's company, sorted newest first. The history displays date, branch, item, variant, SKU, transaction type, quantity, previous stock, new stock, notes, and creator.

## Navigation

The tenant sidebar and responsive navigation include:

- Branches
- Customers
- Categories
- Brands
- Items
- Variants
- Stock In

Links only display when the user has the required permission and the related module is enabled.

## Out of Scope

- Sales/POS
- Invoices
- Services
- Bookings
- Job orders
- Technician incentives
- Purchase orders
- Accounting

## Verification

- `php artisan test --filter=InventoryTransactionHistoryTest`: 1 test passed.
- `php artisan test`: 39 tests passed.
