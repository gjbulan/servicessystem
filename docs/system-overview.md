# System Overview

## MOTOSHOP-SAAS

MOTOSHOP-SAAS is a Laravel 12 multi-tenant SaaS platform that can support motoshops and other service, inventory, sales, and invoice-driven businesses.

## Current Phase

Phase 2 is in progress as the business core foundation. The completed foundation includes:

- Company tenant records.
- User-to-company ownership.
- System and company roles.
- Permission catalog.
- Role-permission assignments.
- User-role assignments.
- Access middleware for company, role, and permission checks.
- Authenticated dashboard foundation summary.
- Company module toggles.
- Super Admin company management.
- Branches.
- Customers.
- Inventory catalog foundation.
- Branch stock and inventory transactions.

## Implemented Application Areas

- `Company` records represent SaaS tenants.
- `User` records may belong to one company. Super admin users may keep `company_id` as `null`.
- `Role` records can be system-level roles with `company_id` as `null`, or future company-owned roles.
- `Permission` records define module/action access keys.
- `role_permissions` assigns permissions to roles.
- `user_roles` assigns roles to users with an optional `branch_id` placeholder for future branch assignment rules.
- Dashboard data is served through `DashboardController`.

## Phase 1.5 Module Toggles

Phase 1.5 adds company-level module toggles so each tenant can enable only the business areas it needs.

- Module records live in `company_modules`.
- `CompanyModule` belongs to `Company`.
- `Company` has helper methods to check, enable, and disable modules.
- `User` can check module access through the assigned company.
- Routes can use `module:module_key` middleware.
- `/settings/modules` lets authorized users manage company module toggles.
- Phase 2 inventory screens use module toggles to protect inventory catalog and stock-in routes.

Business examples:

- Motoshop: `services`, `bookings`, and `job_orders` enabled.
- Solar company: `inventory`, `sales`, and `invoices` enabled, with `bookings` disabled.

## Phase 1.6 Company Management

Phase 1.6 adds Super Admin tenant company management before Phase 2 business modules are built.

- Super Admin users can create, view, update, and soft-delete companies.
- Company slugs auto-generate from the company name when left blank.
- New companies receive default module records through `Company::ensureDefaultModules()`.
- Super Admin users can assign existing users to a company and set user status.
- The database seeder creates Demo Motoshop and a Demo Admin user without removing the existing Super Admin account.
- Company management is protected by the `manage_companies` permission path, with Super Admin bypass support from the existing permission middleware.

The following modules are intentionally out of scope for Phase 1:

- Bookings.
- Inventory.
- Invoices.
- Job orders.
- Subscriptions.

## Phase 2 Business Core

Phase 2 adds tenant business records that can be reused by motoshops, inventory companies, retail stores, and hybrid businesses.

- Branches are company-owned operating locations.
- Customers belong to the company and are shared across branches.
- Items are parent product records.
- Item variants are the actual inventory units.
- Branch stock is tracked per branch and variant.
- Inventory transactions record every stock movement.
- The stock-in page creates stock transactions and shows recent transaction history for the authenticated user's company.

Phase 2 routes are tenant scoped by `company_id` and protected by the existing permission and module middleware.

The following modules remain intentionally out of scope:

- Sales/POS.
- Invoices.
- Services.
- Bookings.
- Job orders.
- Technician incentives.
- Purchase orders.
- Accounting.
