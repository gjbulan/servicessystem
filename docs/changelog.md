# Changelog

## 2026-05-21

- Created Phase 1 documentation set before application code changes.
- Added companies, roles, permissions, role-permission, and user-role database migrations.
- Added nullable `users.company_id` and `users.status`.
- Added `Company`, `Role`, and `Permission` models.
- Updated `User` with company, role, role check, permission check, and super admin helper methods.
- Added company access, role, and permission middleware.
- Registered `company.access`, `role`, and `permission` middleware aliases.
- Added `SaasFoundationSeeder` for default system roles, default permissions, and role-permission assignments.
- Updated `DatabaseSeeder` to run SaaS foundation seeding and assign the local test user to Super Admin.
- Replaced the Breeze dashboard placeholder with a Phase 1 SaaS foundation dashboard.
- Changed `/dashboard` to use `DashboardController`.
- Ran Laravel Pint successfully.
- Ran `php artisan test`: 25 tests passed.
- Ran SQLite `migrate:fresh --seed` verification successfully.
