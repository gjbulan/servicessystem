# Workflow Rules

## Documentation Rule

Every code change must update related markdown documentation.

Each implementation pass should log:

- What was created.
- What was changed.
- Database tables added.
- Models added.
- Relationships added.
- Middleware added.
- Routes added.
- Seeders added.
- Important business rules.

## Phase Scope Rule

Phase 1 must only build the SaaS foundation. Do not build bookings, inventory, invoices, job orders, or subscriptions in this phase.

Phase 1.5 may add module toggle infrastructure, but must not build the actual inventory, sales, invoices, bookings, services, job orders, accounting, purchase order, stock transfer, or technician incentive modules.

## Phase 1 Access Workflow

- Use `company.access` on future tenant routes that must require an active user and active/trial company.
- Use `role:Role Name` on future routes that require at least one matching role.
- Use `permission:permission_key` on future routes that require at least one matching permission.
- Super Admin bypasses role, permission, and company tenant checks.
- Inactive users fail access middleware checks.
- Suspended and expired companies fail company access checks.

## Phase 1.5 Module Toggle Workflow

- Use `module:inventory`, `module:bookings`, `module:services`, or another module key on future module routes.
- The `module` middleware checks the authenticated user's assigned company.
- If a company module is disabled, the request aborts with `403` and `This module is not enabled for your company.`
- Super Admin users with `company_id` as `null` bypass module checks.
- Company users must have an active account and an enabled company module.
- `/settings/modules` requires `auth`, `verified`, `company.access`, and `permission:manage_settings`.
- Company Admin users can manage their own company module toggles.
- Super Admin users can manage module toggles across companies when companies exist.
- Navigation prepares future module links and only renders them when the route exists and the user's company has the module enabled.

## Dashboard Workflow

- `/dashboard` remains authenticated and verified.
- The dashboard shows the current user, company assignment, assigned roles, and foundation counts.
- The dashboard shows enabled modules for users assigned to a company.
- The dashboard does not require company access middleware yet because Phase 1 has no company onboarding flow.
