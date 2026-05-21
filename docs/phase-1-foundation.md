# Phase 1 Foundation

## Goals

Build the base SaaS tenant and RBAC foundation for MOTOSHOP-SAAS.

## Completed Foundation Items

- Companies table and model.
- User company assignment and status.
- Roles and permissions.
- Role-permission pivot table.
- User-role pivot table with optional branch scope placeholder.
- Company access middleware.
- Role checking middleware.
- Permission checking middleware.
- Default system role and permission seeders.
- Dashboard foundation summary.

## Files Created

- `app/Http/Controllers/DashboardController.php`
- `app/Http/Middleware/CheckPermission.php`
- `app/Http/Middleware/CheckRole.php`
- `app/Http/Middleware/EnsureCompanyAccess.php`
- `app/Models/Company.php`
- `app/Models/Permission.php`
- `app/Models/Role.php`
- `database/migrations/2026_05_21_000001_create_companies_table.php`
- `database/migrations/2026_05_21_000002_add_company_and_status_to_users_table.php`
- `database/migrations/2026_05_21_000003_create_roles_and_permissions_tables.php`
- `database/migrations/2026_05_21_000004_create_role_and_user_pivots.php`
- `database/seeders/SaasFoundationSeeder.php`

## Files Changed

- `app/Models/User.php`
- `bootstrap/app.php`
- `database/seeders/DatabaseSeeder.php`
- `resources/views/dashboard.blade.php`
- `routes/web.php`
- `docs/*`

## Middleware Added

- `company.access` mapped to `EnsureCompanyAccess`
- `role` mapped to `CheckRole`
- `permission` mapped to `CheckPermission`

## Routes Changed

- `/dashboard` now uses `DashboardController` instead of an inline closure.

## Seeders Added

- `SaasFoundationSeeder` creates default permissions, system roles, and role-permission assignments.
- `DatabaseSeeder` calls `SaasFoundationSeeder` and creates or updates `test@example.com` as a Super Admin test user.

## Default System Roles

- Super Admin
- Company Admin
- Branch Manager
- Technician
- Cashier
- Inventory Staff

## Default Permissions

- `manage_companies`
- `manage_branches`
- `manage_users`
- `manage_customers`
- `manage_services`
- `manage_bookings`
- `manage_job_orders`
- `manage_invoices`
- `manage_inventory`
- `view_reports`
- `manage_settings`

## Out of Scope

- Bookings.
- Inventory.
- Invoices.
- Job orders.
- Subscriptions.
