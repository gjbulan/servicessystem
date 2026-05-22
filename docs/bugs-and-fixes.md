# Bugs and Fixes

## 2026-05-21

- No application bugs were found during Phase 1 verification.
- `php artisan test` passed with 25 tests.
- SQLite `migrate:fresh --seed` passed for the new migrations and seeders.
- Development note: the local shell does not have `git` available, so file status was verified by direct inspection and command output rather than `git status`.

## 2026-05-21 Phase 1.5

- Fixed module settings view grouping so grouped module definitions preserve `module_key` values and render module rows correctly.
- `php artisan test` passed with 30 tests.
- SQLite `migrate:fresh --seed` passed for Phase 1.5 migrations and seeders.

## 2026-05-22 Phase 1.6

- Fixed feature test stability by disabling Vite asset resolution during tests with `withoutVite()`.
- Fixed company slug generation to check soft-deleted companies so new tenants do not collide with archived unique slugs.
- `php artisan test` passed with 38 tests.
- SQLite `migrate:fresh --seed` passed for Phase 1.6 seed data.
- Local HTTP smoke check was blocked because MySQL on `127.0.0.1:3306` refused connections; start MySQL/XAMPP before manual browser testing.

## 2026-05-22 Phase 2

- Fixed the stock-in page so saved inventory transactions are visible after creation.
- Added a tenant-scoped recent transaction history panel to `/inventory/stock-in`.
- The history list filters by the authenticated user's `company_id` and sorts newest first.
- The history displays date, branch, item, variant, SKU, transaction type, quantity, previous stock, new stock, notes, and creator.
- Added `InventoryTransactionHistoryTest` coverage for the stock-in history fix.
- `php artisan test --filter=InventoryTransactionHistoryTest` passed with 1 test.
- `php artisan test` passed with 39 tests.

## 2026-05-22 Phase 3.5

- Fixed the authentication flow so inactive users cannot log in even with valid credentials.
- Added staff/user soft deletes so removed users no longer appear in normal staff and platform user lists.
- Updated profile account deletion coverage to assert soft deletes instead of hard deletes.
- Added `StaffUserManagementTest` coverage for inactive login blocking.
- `php artisan test --filter=StaffUserManagementTest` passed with 8 tests.
- `php artisan test` passed with 58 tests.

## 2026-05-22 Phase 4

- No Phase 4 service operations bugs were found during focused verification.
- `vendor\bin\pint` passed.
- `php artisan test --filter=ServiceOperationsTest` passed with 7 tests.
- `php artisan test` passed with 65 tests.

## 2026-05-22 Phase 4.5

- Fixed a navigation guard bug where module links controlled only by an explicit `enabled` flag could trigger an undefined `permission` key when rendered for users without incentive access.
- `php artisan test --filter=TechnicianIncentiveTest` passed with 8 tests.
- `vendor\bin\pint` passed.
- `php artisan test` passed with 73 tests.
