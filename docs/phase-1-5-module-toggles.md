# Phase 1.5 Module Toggles

## Purpose

Company module toggles let MOTOSHOP-SAAS support different business types without forcing every tenant into the same workflow set.

Examples:

- Motoshop: enable `services`, `bookings`, and `job_orders`.
- Solar company: enable `inventory`, `sales`, and `invoices`, and disable `bookings`.

This phase only builds toggle infrastructure. It does not build inventory, sales, invoices, bookings, job orders, services, accounting, purchase orders, stock transfers, or technician incentives.

## Database Table Added

### `company_modules`

- `id`
- `company_id`
- `module_key`
- `module_name`
- `description` nullable
- `is_enabled` boolean, default `true`
- `created_at`, `updated_at`

Rules:

- `company_id` belongs to `companies.id`.
- `module_key` is unique per company.
- Company modules are deleted when their company is hard deleted.

## Model Added

- `App\Models\CompanyModule`

Relationships:

- `Company` has many `CompanyModule` records.
- `CompanyModule` belongs to `Company`.

## Helper Methods Added

In `Company`:

- `hasModule($moduleKey)`
- `enableModule($moduleKey)`
- `disableModule($moduleKey)`
- `ensureDefaultModules()`

In `User`:

- `canAccessModule($moduleKey)`

## Middleware Added

- Alias: `module`
- Class: `App\Http\Middleware\EnsureModuleEnabled`

Usage examples:

- `module:inventory`
- `module:bookings`
- `module:services`

If disabled, the middleware aborts with `403` and:

`This module is not enabled for your company.`

Super Admin users with `company_id` as `null` bypass module checks.

## Default Modules

Enabled by default:

- `customers`
- `inventory`
- `sales`
- `invoices`
- `reports`
- `services`
- `bookings`
- `job_orders`
- `technician_incentives`

Disabled by default:

- `accounting`
- `purchase_orders`
- `stock_transfers`

## Seeder Added

- `Database\Seeders\CompanyModuleSeeder`

The seeder creates missing module records for existing companies and preserves existing `is_enabled` choices.

`DatabaseSeeder` now runs:

- `SaasFoundationSeeder`
- `CompanyModuleSeeder`

## Routes Added

- `GET /settings/modules`
- `PATCH /settings/modules/{companyModule}`

Route middleware:

- `auth`
- `verified`
- `company.access`
- `permission:manage_settings`

## UI Added

- `resources/views/settings/modules.blade.php`

Features:

- Lists module toggles by company.
- Lets authorized users enable or disable modules.
- Company users manage only their assigned company.
- Super Admin users with no assigned company can manage all companies.

## Navigation Rule

Navigation now prepares future module links for services, bookings, inventory, sales, and invoices.

Future module links render only when:

- The route exists.
- The authenticated user can access the module.

No actual module routes were added for inventory, sales, invoices, bookings, services, or job orders.

## Verification

- `php artisan test --filter=CompanyModuleTest`: 5 tests passed.
- `php artisan test`: 30 tests passed.
- SQLite `migrate:fresh --seed` passed with the new `company_modules` table.
