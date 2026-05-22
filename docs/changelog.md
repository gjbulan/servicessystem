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
- Added Phase 1.5 company module toggle foundation.
- Added `company_modules` migration.
- Added `CompanyModule` model and default module catalog.
- Updated `Company` with module relationship, default module provisioning, `hasModule`, `enableModule`, and `disableModule`.
- Updated `User` with `canAccessModule`.
- Added `EnsureModuleEnabled` middleware and registered the `module` alias.
- Added `CompanyModuleSeeder` and wired it into `DatabaseSeeder`.
- Added `/settings/modules` routes, controller, and Blade view for module toggles.
- Updated dashboard and navigation logic for module visibility.
- Updated `UserFactory` with Phase 1 user fields.
- Added `CompanyModuleTest` coverage for defaults, settings, toggles, middleware blocking, and Super Admin bypass.
- Ran `php artisan test --filter=CompanyModuleTest`: 5 tests passed.
- Ran `php artisan test`: 30 tests passed.
- Ran SQLite `migrate:fresh --seed` verification successfully with `company_modules`.

## 2026-05-22

- Added Phase 1.6 company management foundation.
- Added `Admin\CompanyController` for Super Admin company CRUD and user assignment.
- Added `/admin/companies` CRUD routes.
- Added `/admin/companies/{company}/users` and `/admin/companies/{company}/users/assign` routes.
- Added company index, create, edit, show, form partial, and user assignment Blade views.
- Added automatic default module creation when companies are created through admin CRUD.
- Updated `DatabaseSeeder` to create Demo Motoshop and `admin@demo.com` as a Company Admin.
- Updated navigation with a Companies link for Super Admin or users with `manage_companies`.
- Updated dashboard to show platform totals for Super Admin users and tenant information for company users.
- Updated feature tests to bypass Vite in the test environment.
- Added `AdminCompanyManagementTest` coverage for access, CRUD, soft delete, assignment, and demo seeding.
- Updated company slug checks to account for soft-deleted companies that still hold unique slugs.
- Ran Laravel Pint successfully.
- Ran `php artisan test --filter=AdminCompanyManagementTest`: 8 tests passed.
- Ran `php artisan test`: 38 tests passed.
- Ran SQLite `migrate:fresh --seed` verification successfully.
