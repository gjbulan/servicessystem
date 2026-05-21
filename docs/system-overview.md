# System Overview

## MOTOSHOP-SAAS

MOTOSHOP-SAAS is a Laravel 12 multi-tenant SaaS platform that can support motoshops and other service, inventory, sales, and invoice-driven businesses.

## Current Phase

Phase 1 is implemented as the SaaS foundation:

- Company tenant records.
- User-to-company ownership.
- System and company roles.
- Permission catalog.
- Role-permission assignments.
- User-role assignments.
- Access middleware for company, role, and permission checks.
- Authenticated dashboard foundation summary.

## Implemented Application Areas

- `Company` records represent SaaS tenants.
- `User` records may belong to one company. Super admin users may keep `company_id` as `null`.
- `Role` records can be system-level roles with `company_id` as `null`, or future company-owned roles.
- `Permission` records define module/action access keys.
- `role_permissions` assigns permissions to roles.
- `user_roles` assigns roles to users with an optional `branch_id` placeholder for a later branch module.
- Dashboard data is served through `DashboardController`.

## Phase 1.5 Module Toggles

Phase 1.5 adds company-level module toggles so each tenant can enable only the business areas it needs.

- Module records live in `company_modules`.
- `CompanyModule` belongs to `Company`.
- `Company` has helper methods to check, enable, and disable modules.
- `User` can check module access through the assigned company.
- Routes can use `module:module_key` middleware.
- `/settings/modules` lets authorized users manage company module toggles.

Business examples:

- Motoshop: `services`, `bookings`, and `job_orders` enabled.
- Solar company: `inventory`, `sales`, and `invoices` enabled, with `bookings` disabled.

The following modules are intentionally out of scope for Phase 1:

- Bookings.
- Inventory.
- Invoices.
- Job orders.
- Subscriptions.
