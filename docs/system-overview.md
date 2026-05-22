# System Overview

## MOTOSHOP-SAAS

MOTOSHOP-SAAS is a Laravel 12 multi-tenant SaaS platform that can support motoshops and other service, inventory, sales, and invoice-driven businesses.

## Current Phase

Phase 5 adds Accounting Lite on top of the completed SaaS, inventory, sales, user management, service operations, and technician incentive foundations. The completed foundation includes:

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
- Optional inventory variant mode.
- Sales, sale items, payments, and receipt/invoice printing.
- Company staff management.
- Platform user management.
- Asset types and customer assets.
- Service categories and services.
- Public booking and booking management.
- Job orders with multiple technicians, services, items, stock usage, and service history.
- Technician incentives generated from completed job order services.
- Expense categories, expenses, and Accounting Lite financial reports.

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

## Phase 2.5 Optional Variant Mode

Phase 2.5 adds company-level inventory settings for simple and variant-heavy businesses.

- `company_inventory_settings` stores whether a company uses item variants.
- `Company::usesItemVariants()` exposes the setting to controllers and views.
- Variant mode defaults to enabled for existing and new companies.
- When variants are disabled, the item form collects SKU, barcode, prices, and unit type.
- When variants are disabled, each item automatically receives one hidden `Default` variant.
- Stock, branch stock, and inventory transactions continue to use `item_variant_id` internally.
- The Variants navigation link is hidden when variants are disabled.
- Stock In shows item labels instead of variant labels when variants are disabled.

## Phase 3 Sales & Invoicing Core

Phase 3 adds tenant sales records and payment handling.

- `sales` stores branch, customer, totals, status, amount paid, and balance due.
- `sale_items` stores sold `item_variant_id` values and item snapshots.
- `sale_payments` stores payment records.
- Sales can be saved as draft or unpaid.
- Payments move sales to partial or paid.
- Inventory is deducted only once when a sale becomes paid.
- Sale stock deduction writes `inventory_transactions` with `transaction_type = sale`.
- `/sales/{sale}/print` provides a print-friendly receipt/invoice page.

Phase 3 does not create a separate invoices table yet.

## Phase 3.5 Staff & User Management

Phase 3.5 adds staff management for tenant companies and platform-level user management for Super Admin users.

- Company users with `manage_users` can manage staff through `/staff`.
- Company staff queries are scoped to the authenticated user's `company_id`.
- Company Admin users cannot assign or manage Super Admin users.
- Staff users are assigned one role and one optional branch through `user_roles`.
- Super Admin users manage platform users through `/admin/users`.
- Super Admin users can create platform Super Admins with `company_id = null`.
- Users now soft delete through `users.deleted_at`.
- Inactive users are blocked from logging in.
- Dashboard cards show staff totals for company users and platform user totals for Super Admin users.

## Phase 4 Service Operations

Phase 4 adds service workflows for motoshops, solar service, repair shops, and other companies that need customer-owned asset service records.

- Asset types are tenant-defined, so the system avoids hardcoded motorcycle-only assumptions.
- Customer assets belong to customers and can represent motorcycles, vehicles, solar systems, equipment, devices, or other tenant-defined assets.
- Service categories and services provide a tenant service catalog.
- Public booking uses `/book/{company:slug}` and stores customer, asset, and service snapshots without requiring customer registration.
- Booking confirmation creates or updates a customer, creates or updates a customer asset, and creates a job order.
- Job orders support multiple technician assignments.
- Job orders can store performed services and optional inventory items used.
- Inventory items are deducted only when a job order is completed.
- Job order completion writes `inventory_transactions` with `transaction_type = job_order_usage`.
- Job order completion creates a customer asset service history record.

## Phase 4.5 Technician Incentives

Phase 4.5 tracks technician incentives without adding payroll or accounting.

- Services expose `default_incentive_amount` in the setup UI.
- Completing a job order generates technician incentives only when the `technician_incentives` module is enabled.
- Incentives are generated per assigned Technician user per completed job order service.
- Incentive generation is idempotent, so repeated completion does not duplicate rows.
- Branch Managers and Company Admins can override unpaid incentives with a reason.
- Company Admins can approve incentives and mark approved incentives as paid.
- Technician users can view only their own incentive records.
- Dashboard cards show pending, approved unpaid, and paid-this-month totals.

## Phase 5 Accounting Lite

Phase 5 provides basic financial visibility without a full ERP accounting system.

- Expense categories organize tenant operating expenses.
- Expenses can be recorded as company-wide or assigned to a branch.
- Optional receipt attachments can be uploaded with expenses.
- Financial Summary shows paid-sales revenue, COGS, gross profit, operating expenses, paid technician incentives, net profit, outstanding balances, and record counts.
- Income Statement shows the same core financial lines in statement format.
- Branch Profitability compares revenue, COGS, expenses, paid incentives, and net profit by branch.
- Revenue is recognized from paid sales only.
- Completed job order service prices are excluded from revenue in Phase 5 to avoid double counting.
- Accounting routes and navigation are controlled by the `accounting` company module.

The following modules remain intentionally out of scope:

- Purchase orders.
- Subscriptions.
- Advanced reports.
