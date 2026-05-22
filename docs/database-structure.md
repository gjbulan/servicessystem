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

`branch_id` is only a scope placeholder. No branches table or branch module was created in Phase 1.

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
- A user belongs to one company.
- A user belongs to many roles through `user_roles`.
- A role belongs to one company.
- A role belongs to many users through `user_roles`.
- A role belongs to many permissions through `role_permissions`.
- A permission belongs to many roles through `role_permissions`.
- A company module belongs to one company.

## Phase 1.6 Company Management

Phase 1.6 adds no new database tables. It uses:

- `companies` for tenant CRUD.
- `company_modules` for automatically created default module toggles.
- `users.company_id` and `users.status` for assigning existing users to companies.
- `user_roles` for the seeded Demo Admin `Company Admin` role assignment.

Company records are soft deleted through `companies.deleted_at`.
