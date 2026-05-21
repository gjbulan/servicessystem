# System Overview

## MOTOSHOP-SAAS

MOTOSHOP-SAAS is a Laravel 12 multi-tenant SaaS platform for motoshop service operations.

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

The following modules are intentionally out of scope for Phase 1:

- Bookings.
- Inventory.
- Invoices.
- Job orders.
- Subscriptions.
