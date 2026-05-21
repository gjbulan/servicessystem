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

## Phase 1 Access Workflow

- Use `company.access` on future tenant routes that must require an active user and active/trial company.
- Use `role:Role Name` on future routes that require at least one matching role.
- Use `permission:permission_key` on future routes that require at least one matching permission.
- Super Admin bypasses role, permission, and company tenant checks.
- Inactive users fail access middleware checks.
- Suspended and expired companies fail company access checks.

## Dashboard Workflow

- `/dashboard` remains authenticated and verified.
- The dashboard shows the current user, company assignment, assigned roles, and foundation counts.
- The dashboard does not require company access middleware yet because Phase 1 has no company onboarding flow.
