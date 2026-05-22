# Database Structure

## Phase 1 Tables

### `companies`

- `id`
- `name`
- `slug` unique
- `email` nullable
- `phone` nullable
- `address` nullable
- `status` enum: `active`, `trial`, `suspended`, `expired`; default `trial`
- `created_at`, `updated_at`
- `deleted_at` for soft deletes

### `users`

The Breeze `users` table now also has:

- `company_id` nullable foreign key to `companies.id`, nulls on company delete
- `status` string, default `active`, indexed

### `roles`

- `id`
- `company_id` nullable foreign key to `companies.id`, cascades on hard delete
- `name`
- `description` nullable
- `is_system_role` boolean, default `false`
- `created_at`, `updated_at`
- unique index on `company_id`, `name`
- index on `company_id`, `is_system_role`

### `permissions`

- `id`
- `module`
- `action`
- `key` unique
- `description` nullable
- `created_at`, `updated_at`
- unique index on `module`, `action`

### `role_permissions`

- `role_id` foreign key to `roles.id`, cascades on delete
- `permission_id` foreign key to `permissions.id`, cascades on delete
- primary key on `role_id`, `permission_id`

### `user_roles`

- `user_id` foreign key to `users.id`, cascades on delete
- `role_id` foreign key to `roles.id`, cascades on delete
- `branch_id` nullable unsigned big integer, indexed
- unique index on `user_id`, `role_id`, `branch_id`

`branch_id` is a role scope placeholder for future branch assignment rules. The Phase 2 `branches` table now exists, but user branch assignment workflows are not built yet.

### `company_modules`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `module_key`
- `module_name`
- `description` nullable
- `is_enabled` boolean, default `true`
- `created_at`, `updated_at`
- unique index on `company_id`, `module_key`
- index on `company_id`, `is_enabled`

Default module keys:

- `customers`
- `inventory`
- `sales`
- `invoices`
- `reports`
- `services`
- `bookings`
- `job_orders`
- `technician_incentives`
- `accounting`
- `purchase_orders`
- `stock_transfers`

## Relationships

- A company has many users.
- A company has many roles.
- A company has many company modules.
- A company has many branches.
- A company has many customers.
- A company has many item categories.
- A company has many item brands.
- A company has many items.
- A company has many item variants.
- A company has one inventory setting.
- A user belongs to one company.
- A user belongs to many roles through `user_roles`.
- A role belongs to one company.
- A role belongs to many users through `user_roles`.
- A role belongs to many permissions through `role_permissions`.
- A permission belongs to many roles through `role_permissions`.
- A company module belongs to one company.
- A branch belongs to one company.
- A branch has many branch item variant stocks.
- A branch has many inventory transactions.
- A customer belongs to one company.
- An item category belongs to one company and has many items.
- An item brand belongs to one company and has many items.
- An item belongs to one company, category, and brand.
- An item has many item variants.
- An item variant belongs to one company and item.
- An item variant has many branch stock records.
- An item variant has many inventory transactions.
- A branch item variant stock belongs to one company, branch, and item variant.
- An inventory transaction belongs to one company, branch, item variant, and creator user.
- A company inventory setting belongs to one company.

## Phase 1.6 Company Management

Phase 1.6 adds no new database tables. It uses:

- `companies` for tenant CRUD.
- `company_modules` for automatically created default module toggles.
- `users.company_id` and `users.status` for assigning existing users to companies.
- `user_roles` for the seeded Demo Admin `Company Admin` role assignment.

Company records are soft deleted through `companies.deleted_at`.

## Phase 2 Business Core

### `branches`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `name`
- `code` nullable
- `email` nullable
- `phone` nullable
- `address` nullable
- `manager_name` nullable
- `operating_hours` nullable
- `status` string: `active` or `inactive`; default `active`
- `created_at`, `updated_at`
- `deleted_at` for soft deletes
- unique index on `company_id`, `code`

Branches are tenant scoped by `company_id`.

### `customers`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `customer_code` nullable
- `name`
- `phone` nullable
- `email` nullable
- `address` nullable
- `notes` nullable
- `status` string: `active` or `inactive`; default `active`
- `created_at`, `updated_at`
- `deleted_at` for soft deletes
- unique index on `company_id`, `customer_code`

Customers belong to a company, not a branch, so they can be shared across all tenant branches.

### `item_categories`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `name`
- `description` nullable
- `status` string; default `active`
- `sort_order` nullable
- `created_at`, `updated_at`
- `deleted_at` for soft deletes

### `item_brands`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `name`
- `description` nullable
- `status` string; default `active`
- `created_at`, `updated_at`
- `deleted_at` for soft deletes

### `items`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `item_category_id` nullable foreign key to `item_categories.id`, nulls on delete
- `item_brand_id` nullable foreign key to `item_brands.id`, nulls on delete
- `name`
- `description` nullable
- `status` string; default `active`
- `created_at`, `updated_at`
- `deleted_at` for soft deletes

Items are parent product records. Stock is tracked on variants, not items.

### `item_variants`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `item_id` foreign key to `items.id`, cascades on hard delete
- `variant_name`
- `sku` nullable
- `barcode` nullable
- `cost_price` decimal(12,2), default `0`
- `selling_price` decimal(12,2), default `0`
- `unit_type` nullable
- `attributes_json` nullable JSON
- `status` string; default `active`
- `created_at`, `updated_at`
- `deleted_at` for soft deletes
- unique index on `company_id`, `sku`
- unique index on `company_id`, `barcode`

Variants are the actual inventory units for search, pricing, stock, and transactions.

### `branch_item_variant_stocks`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `branch_id` foreign key to `branches.id`, cascades on hard delete
- `item_variant_id` foreign key to `item_variants.id`, cascades on hard delete
- `current_stock` decimal(12,2), default `0`
- `low_stock_threshold` decimal(12,2), default `0`
- `created_at`, `updated_at`
- unique index on `company_id`, `branch_id`, `item_variant_id`

Stock is tracked per branch and item variant.

### `inventory_transactions`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `branch_id` foreign key to `branches.id`, cascades on hard delete
- `item_variant_id` foreign key to `item_variants.id`, cascades on hard delete
- `transaction_type` indexed string
- `quantity` decimal(12,2)
- `previous_stock` decimal(12,2)
- `new_stock` decimal(12,2)
- `reference_type` nullable
- `reference_id` nullable
- `notes` nullable
- `created_by` nullable foreign key to `users.id`, nulls on delete
- `created_at`, `updated_at`

Allowed transaction types:

- `initial_stock`
- `stock_in`
- `manual_adjustment`
- `damage`
- `return`

The stock-in page displays the latest tenant-scoped inventory transactions newest first.

## Phase 2.5 Optional Variant Mode

### `company_inventory_settings`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `enable_item_variants` boolean, default `true`
- `created_at`, `updated_at`
- unique index on `company_id`

Existing companies receive a setting row with `enable_item_variants = true` during migration. New companies receive the same default setting automatically.

When `enable_item_variants` is disabled, the UI hides separate variant management and stores SKU, barcode, cost, selling price, and unit type on a single `item_variants` row with `variant_name = Default`.

The inventory database remains variant-based. `branch_item_variant_stocks` and `inventory_transactions` continue to use `item_variant_id`.
