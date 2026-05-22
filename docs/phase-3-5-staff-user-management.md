# Phase 3.5 Staff & User Management

## Purpose

Phase 3.5 adds staff and platform user management.

Company Admin users can create and manage users inside their own company. Super Admin users can view and manage platform users across companies.

This phase does not build services, bookings, job orders, technician incentives, purchase orders, accounting, or subscriptions.

## Database Changes

No new staff tables were added.

The existing `users` table now supports soft deletes:

- `deleted_at`

Existing tables used:

- `users`
- `roles`
- `user_roles`
- `branches`
- `companies`

## User Model Changes

- `App\Models\User` now uses `SoftDeletes`.
- Soft-deleted users are excluded from normal user queries and authentication lookups.

## Auth Changes

Inactive users are now blocked from logging in.

If a user enters valid credentials but `users.status = inactive`, the login request logs the user out immediately and returns a validation error.

## Company Staff Management

Controller:

- `App\Http\Controllers\StaffController`

Routes:

- `GET /staff`
- `GET /staff/create`
- `POST /staff`
- `GET /staff/{user}`
- `GET /staff/{user}/edit`
- `PUT/PATCH /staff/{user}`
- `DELETE /staff/{user}`

Middleware:

- `auth`
- `verified`
- `company.access`
- `permission:manage_users`

Company staff fields:

- `name`
- `email`
- `password`
- `password_confirmation`
- `status`
- `role_id`
- `branch_id` nullable

Edit staff fields:

- `name`
- `email`
- `status`
- `role_id`
- `branch_id` nullable
- optional `password`
- optional `password_confirmation`

## Company Staff Rules

- Company staff queries are filtered by the authenticated user's `company_id`.
- Company Admin users can only manage users inside their own company.
- Company users cannot view users from another company.
- Created staff users receive the authenticated user's `company_id`.
- Company Admin users cannot assign the `Super Admin` role.
- Company Admin users cannot edit or delete Super Admin users.
- Company Admin users cannot delete themselves.
- Passwords are hashed.
- If password is blank during edit, the existing password is kept.
- Email must be unique.
- Staff deletion uses soft delete.

Allowed company roles:

- Company Admin
- Branch Manager
- Technician
- Cashier
- Inventory Staff

## Platform User Management

Controller:

- `App\Http\Controllers\Admin\UserController`

Routes:

- `GET /admin/users`
- `GET /admin/users/create`
- `POST /admin/users`
- `GET /admin/users/{user}`
- `GET /admin/users/{user}/edit`
- `PUT/PATCH /admin/users/{user}`
- `DELETE /admin/users/{user}`

Middleware:

- `auth`
- `verified`
- `role:Super Admin`

Super Admin users can:

- view users across companies
- filter users by company
- create users for any company
- create platform Super Admin users with no company
- assign roles
- assign one branch through `user_roles.branch_id`
- activate or deactivate users
- reset or change passwords
- soft-delete users

The last Super Admin user cannot be deleted.

## Branch Assignment

Phase 3.5 supports one branch assignment per staff user.

Branch assignment is stored on the existing `user_roles.branch_id` pivot column. Multiple branch assignment is not yet supported.

For platform users:

- Non-Super Admin roles require a company.
- Assigned branches must belong to the selected company.
- Super Admin role assignment clears `company_id` and `branch_id`.

## Dashboard Updates

Company users see staff totals:

- total staff
- active staff
- inactive staff

Super Admin users see platform user totals:

- total platform users
- active users
- inactive users
- users by company

## Navigation

Company users with `manage_users` see:

- Staff

Super Admin users see:

- Platform Users

## Files Created

- `app/Http/Controllers/StaffController.php`
- `app/Http/Controllers/Admin/UserController.php`
- `database/migrations/2026_05_22_000007_add_soft_deletes_to_users_table.php`
- `resources/views/staff/_form.blade.php`
- `resources/views/staff/index.blade.php`
- `resources/views/staff/create.blade.php`
- `resources/views/staff/edit.blade.php`
- `resources/views/staff/show.blade.php`
- `resources/views/admin/users/_form.blade.php`
- `resources/views/admin/users/index.blade.php`
- `resources/views/admin/users/create.blade.php`
- `resources/views/admin/users/edit.blade.php`
- `resources/views/admin/users/show.blade.php`
- `tests/Feature/StaffUserManagementTest.php`

## Files Changed

- `app/Models/User.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard.blade.php`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/layouts/sidebar.blade.php`
- `routes/web.php`
- `docs/*`

## Verification

- `php artisan test --filter=StaffUserManagementTest`: 8 tests passed.
- `php artisan test`: 58 tests passed.
