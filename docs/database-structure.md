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
- `deleted_at` for soft deletes

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

`branch_id` stores the single branch assignment used by Phase 3.5 staff management. Multiple branch assignment is not supported yet.

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
- A company has many asset types.
- A company has many customer assets.
- A company has many item categories.
- A company has many item brands.
- A company has many items.
- A company has many item variants.
- A company has many sales.
- A company has many service categories.
- A company has many services.
- A company has many bookings.
- A company has many job orders.
- A company has many customer asset service histories.
- A company has one inventory setting.
- A user belongs to one company.
- A user belongs to many roles through `user_roles`.
- A user can create many job orders.
- A user can be assigned to many job orders as a technician.
- A role belongs to one company.
- A role belongs to many users through `user_roles`.
- A role belongs to many permissions through `role_permissions`.
- A permission belongs to many roles through `role_permissions`.
- A company module belongs to one company.
- A branch belongs to one company.
- A branch has many branch item variant stocks.
- A branch has many inventory transactions.
- A branch has many sales.
- A branch has many bookings.
- A branch has many job orders.
- A branch has many customer asset service histories.
- A customer belongs to one company.
- A customer has many sales.
- A customer has many customer assets.
- A customer has many bookings.
- A customer has many job orders.
- A customer has many service histories.
- An asset type belongs to one company and has many customer assets.
- A customer asset belongs to one company, customer, and optional asset type.
- A customer asset has many bookings, job orders, and service histories.
- An item category belongs to one company and has many items.
- An item brand belongs to one company and has many items.
- An item belongs to one company, category, and brand.
- An item has many item variants.
- An item variant belongs to one company and item.
- An item variant has many branch stock records.
- An item variant has many inventory transactions.
- An item variant has many sale items.
- An item variant has many job order items.
- A branch item variant stock belongs to one company, branch, and item variant.
- An inventory transaction belongs to one company, branch, item variant, and creator user.
- A company inventory setting belongs to one company.
- A sale belongs to one company, branch, customer, and creator user.
- A sale has many sale items and sale payments.
- A sale item belongs to one company, sale, and item variant.
- A sale payment belongs to one company, sale, and receiver user.
- A service category belongs to one company and has many services.
- A service belongs to one company and optional service category.
- A service has many booking service and job order service rows.
- A booking belongs to one company, branch, optional customer, and optional customer asset.
- A booking has many booking services and one job order.
- A booking service belongs to one booking and service.
- A job order belongs to one company, branch, customer, optional booking, optional customer asset, and optional creator user.
- A job order has many technicians, services, items, and one service history.
- A job order technician belongs to one job order and one user.
- A job order service belongs to one job order and optional service.
- A job order item belongs to one job order and optional item variant.
- A customer asset service history belongs to one company, branch, customer, optional customer asset, and job order.

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
- `sale`
- `job_order_usage`

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

## Phase 3 Sales & Invoicing Core

### `sales`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `branch_id` foreign key to `branches.id`, cascades on hard delete
- `customer_id` nullable foreign key to `customers.id`, nulls on delete
- `sale_number`
- `status` string: `draft`, `unpaid`, `partial`, `paid`, `void`
- `sale_date`
- `subtotal` decimal(12,2), default `0`
- `discount_amount` decimal(12,2), default `0`
- `tax_amount` decimal(12,2), default `0`
- `total` decimal(12,2), default `0`
- `amount_paid` decimal(12,2), default `0`
- `balance_due` decimal(12,2), default `0`
- `notes` nullable
- `created_by` nullable foreign key to `users.id`, nulls on delete
- `created_at`, `updated_at`
- `deleted_at` for soft deletes
- unique index on `company_id`, `sale_number`

### `sale_items`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `sale_id` foreign key to `sales.id`, cascades on hard delete
- `item_variant_id` foreign key to `item_variants.id`, cascades on hard delete
- `item_name_snapshot`
- `variant_name_snapshot` nullable
- `sku_snapshot` nullable
- `quantity` decimal(12,2)
- `unit_price` decimal(12,2)
- `cost_price_snapshot` decimal(12,2)
- `line_total` decimal(12,2)
- `created_at`, `updated_at`

Sale item snapshots preserve item and pricing details even if catalog records change later.

### `sale_payments`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `sale_id` foreign key to `sales.id`, cascades on hard delete
- `payment_method` nullable
- `reference_number` nullable
- `amount` decimal(12,2)
- `paid_at`
- `received_by` nullable foreign key to `users.id`, nulls on delete
- `notes` nullable
- `created_at`, `updated_at`

Paid sales create `inventory_transactions` rows with `transaction_type = sale`, negative quantities, `reference_type = Sale`, and `reference_id` set to the sale ID.

## Phase 3.5 Staff & User Management

Phase 3.5 adds no new staff tables.

Database changes:

- `users.deleted_at` was added for soft deletes.

Existing tables used:

- `users`
- `roles`
- `user_roles`
- `branches`
- `companies`

Staff role and branch assignment:

- `user_roles.role_id` stores the selected role.
- `user_roles.branch_id` stores one optional branch assignment.
- Company staff management supports only one role and one branch assignment per user for now.

Soft-deleted users are excluded from normal staff and platform user lists.

## Phase 4 Service Operations

### `asset_types`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `name`
- `description` nullable
- `status` string: `active` or `inactive`; default `active`
- `created_at`, `updated_at`
- `deleted_at` for soft deletes
- index on `company_id`, `status`

Asset types replace hardcoded motorcycle assumptions with tenant-defined assets such as Motorcycle, Solar System, Equipment, Vehicle, or Device.

### `customer_assets`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `customer_id` foreign key to `customers.id`, cascades on hard delete
- `asset_type_id` nullable foreign key to `asset_types.id`, nulls on delete
- `name` nullable
- `brand` nullable
- `model` nullable
- `year` nullable
- `serial_number` nullable
- `plate_number` nullable
- `color` nullable
- `notes` nullable
- `status` string: `active` or `inactive`; default `active`
- `created_at`, `updated_at`
- `deleted_at` for soft deletes
- indexes on `company_id`, `customer_id` and `company_id`, `status`

Customers can have multiple assets.

### `service_categories`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `name`
- `description` nullable
- `status` string: `active` or `inactive`; default `active`
- `sort_order` nullable
- `created_at`, `updated_at`
- `deleted_at` for soft deletes

### `services`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `service_category_id` nullable foreign key to `service_categories.id`, nulls on delete
- `name`
- `description` nullable
- `default_price` decimal(12,2), default `0`
- `estimated_duration_minutes` nullable unsigned integer
- `default_incentive_amount` nullable decimal(12,2)
- `status` string: `active` or `inactive`; default `active`
- `created_at`, `updated_at`
- `deleted_at` for soft deletes

`default_incentive_amount` is stored for later phases only. Phase 4 does not calculate technician incentives.

### `bookings`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `branch_id` foreign key to `branches.id`, cascades on hard delete
- `booking_reference`
- `customer_id` nullable foreign key to `customers.id`, nulls on delete
- `customer_asset_id` nullable foreign key to `customer_assets.id`, nulls on delete
- `customer_name`
- `phone`
- `email` nullable
- `asset_type_name` nullable
- `asset_details_json` nullable JSON
- `preferred_datetime` nullable datetime
- `issue_description` nullable
- `lead_source` nullable
- `status` string: `pending`, `confirmed`, `no_show`, `in_progress`, `completed`, `cancelled`; default `pending`
- `internal_notes` nullable
- `created_at`, `updated_at`
- `deleted_at` for soft deletes
- unique index on `company_id`, `booking_reference`

Public bookings store customer and asset snapshot details. They do not create a customer until confirmed.

### `booking_services`

- `id`
- `booking_id` foreign key to `bookings.id`, cascades on delete
- `service_id` foreign key to `services.id`, cascades on delete
- `service_name_snapshot`
- `price_snapshot` decimal(12,2)
- `created_at`, `updated_at`

### `job_orders`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `branch_id` foreign key to `branches.id`, cascades on hard delete
- `booking_id` nullable foreign key to `bookings.id`, nulls on delete
- `customer_id` foreign key to `customers.id`, cascades on hard delete
- `customer_asset_id` nullable foreign key to `customer_assets.id`, nulls on delete
- `job_order_number`
- `status` string: `open`, `checked_in`, `in_progress`, `waiting_approval`, `waiting_parts`, `completed`, `cancelled`; default `open`
- `customer_complaint` nullable
- `inspection_notes` nullable
- `internal_notes` nullable
- `approval_status` nullable
- `approval_notes` nullable
- `started_at` nullable
- `completed_at` nullable
- `created_by` nullable foreign key to `users.id`, nulls on delete
- `created_at`, `updated_at`
- `deleted_at` for soft deletes
- unique index on `company_id`, `job_order_number`

### `job_order_technicians`

- `id`
- `job_order_id` foreign key to `job_orders.id`, cascades on delete
- `technician_id` foreign key to `users.id`, cascades on delete
- `role` nullable
- `is_primary` boolean, default `false`
- `notes` nullable
- `created_at`, `updated_at`
- unique index on `job_order_id`, `technician_id`

### `job_order_services`

- `id`
- `job_order_id` foreign key to `job_orders.id`, cascades on delete
- `service_id` nullable foreign key to `services.id`, nulls on delete
- `service_name_snapshot`
- `price_snapshot` decimal(12,2)
- `notes` nullable
- `status` nullable
- `created_at`, `updated_at`

### `job_order_items`

- `id`
- `job_order_id` foreign key to `job_orders.id`, cascades on delete
- `item_variant_id` nullable foreign key to `item_variants.id`, nulls on delete
- `item_name_snapshot`
- `variant_name_snapshot` nullable
- `sku_snapshot` nullable
- `quantity` decimal(12,2)
- `cost_price_snapshot` decimal(12,2)
- `selling_price_snapshot` decimal(12,2)
- `notes` nullable
- `created_at`, `updated_at`

Job order item stock is deducted only when the job order is completed.

### `customer_asset_service_histories`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `branch_id` foreign key to `branches.id`, cascades on hard delete
- `customer_id` foreign key to `customers.id`, cascades on hard delete
- `customer_asset_id` nullable foreign key to `customer_assets.id`, nulls on delete
- `job_order_id` foreign key to `job_orders.id`, cascades on delete
- `service_summary`
- `service_date`
- `notes` nullable
- `created_at`, `updated_at`
- unique index on `job_order_id`

One service history record is generated when a job order is completed.
