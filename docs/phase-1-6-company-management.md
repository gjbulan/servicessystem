# Phase 1.6 Company Management

## Purpose

Phase 1.6 gives Super Admin users a tenant company management surface before Phase 2 business modules are built.

This phase only builds company management. It does not build branches, customers, inventory, sales, invoices, bookings, job orders, or subscriptions.

## Database Changes

No new database tables were added.

Existing tables used:

- `companies`
- `company_modules`
- `users`
- `roles`
- `user_roles`

## Controller Added

- `App\Http\Controllers\Admin\CompanyController`

Controller actions:

- `index`
- `create`
- `store`
- `show`
- `edit`
- `update`
- `destroy`
- `users`
- `assignUser`

## Views Added

- `resources/views/admin/companies/index.blade.php`
- `resources/views/admin/companies/create.blade.php`
- `resources/views/admin/companies/edit.blade.php`
- `resources/views/admin/companies/show.blade.php`
- `resources/views/admin/companies/users.blade.php`
- `resources/views/admin/companies/_form.blade.php`

## Routes Added

- `GET /admin/companies`
- `GET /admin/companies/create`
- `POST /admin/companies`
- `GET /admin/companies/{company}`
- `GET /admin/companies/{company}/edit`
- `PUT/PATCH /admin/companies/{company}`
- `DELETE /admin/companies/{company}`
- `GET /admin/companies/{company}/users`
- `POST /admin/companies/{company}/users/assign`

Route middleware:

- `auth`
- `verified`
- `permission:manage_companies`

## Company Rules

- Fields: `name`, `slug`, `email`, `phone`, `address`, `status`.
- Valid statuses: `active`, `trial`, `suspended`, `expired`.
- Slug auto-generates from company name when the slug field is empty.
- Manually entered slugs must be unique.
- Slug uniqueness checks include soft-deleted companies because the database unique index still reserves archived slugs.
- Company delete uses soft delete.
- Company create and update call `Company::ensureDefaultModules()`.

## Default Company Modules

When a company is created, default modules are created with these initial states:

- `customers`: enabled
- `inventory`: enabled
- `sales`: enabled
- `invoices`: enabled
- `reports`: enabled
- `services`: enabled
- `bookings`: enabled
- `job_orders`: enabled
- `technician_incentives`: enabled
- `accounting`: disabled
- `purchase_orders`: disabled
- `stock_transfers`: disabled

## User Assignment Rules

- Super Admin can assign an existing user to a company.
- Assignment updates `users.company_id`.
- Assignment updates `users.status` to `active` or `inactive`.
- No invitation or email system was added.
- Super Admin users may remain with `company_id` as `null`.

## Seeder Updates

`DatabaseSeeder` now creates:

- Super Admin: `test@example.com` / `password`
- Company: Demo Motoshop
- Demo Admin: `admin@demo.com` / `password`

Demo Motoshop:

- `name`: Demo Motoshop
- `slug`: demo-motoshop
- `email`: demo@example.com
- `status`: active

Demo Admin:

- assigned to Demo Motoshop
- assigned the `Company Admin` role
- status `active`

## Navigation

The main navigation now shows `Companies` only when the authenticated user:

- is Super Admin, or
- has the `manage_companies` permission.

## Dashboard

Super Admin users see platform totals:

- total companies
- total module records
- roles count
- permissions count

Company users continue to see:

- company name
- enabled modules
- assigned roles

## Tenant Safety

Normal company users cannot access `/admin/companies`.

Access is controlled with `permission:manage_companies`. The existing permission middleware allows Super Admin users to bypass direct permission checks.

## Verification

- `php artisan test --filter=AdminCompanyManagementTest`: 8 tests passed.
- `php artisan test`: 38 tests passed.
- SQLite `migrate:fresh --seed` passed.
