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

Phase 1.6 may add company management infrastructure, but must not build branches, customers, inventory, sales, invoices, bookings, job orders, or subscriptions.

Phase 2 may build branches, customers, item catalog records, variant-based inventory, branch stock, and inventory transactions. It must not build sales, POS, invoices, services, bookings, job orders, technician incentives, purchase orders, or accounting.

Phase 2.5 may add optional item variant UI behavior. It must keep the inventory database variant-based and must not build sales, POS, invoices, services, bookings, job orders, technician incentives, purchase orders, or accounting.

Phase 3 may build sales, sale line items, payments, paid-sale stock deduction, and a print-friendly receipt/invoice page. It must not build services, bookings, job orders, technician incentives, purchase orders, or accounting.

Phase 3.5 may build company staff management, platform user management, branch assignment for staff, inactive-login blocking, and staff dashboard totals. It must not build services, bookings, job orders, incentives, purchase orders, accounting, or subscriptions.

Phase 4 may build asset types, customer assets, service categories, services, public booking, bookings, job orders, job order technicians, job order services/items, and service history. It must not build technician incentives, purchase orders, accounting, subscriptions, or advanced reports.

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

## Phase 1.6 Company Management Workflow

- `/admin/companies` and related company management routes require `auth`, `verified`, and `permission:manage_companies`.
- Super Admin users can access company management through the existing Super Admin bypass in permission middleware.
- Normal company users cannot access `/admin/companies` unless they are intentionally granted `manage_companies`.
- Company slugs auto-generate from company names when the slug field is empty.
- Manually entered slugs must be unique.
- Deleting a company performs a soft delete.
- Creating or updating a company ensures default company module records exist.
- User assignment updates `users.company_id` and `users.status`.
- The user assignment screen does not send invitations or emails.
- Super Admin users may remain with `company_id` as `null`.

## Dashboard Workflow

- `/dashboard` remains authenticated and verified.
- The dashboard shows the current user, company assignment, assigned roles, and foundation counts.
- The dashboard shows enabled modules for users assigned to a company.
- Super Admin users see platform totals for companies, module records, roles, and permissions.
- The dashboard does not require company access middleware yet because Phase 1 has no company onboarding flow.

## Phase 2 Business Core Workflow

- Branch, customer, and inventory routes require authenticated and verified users.
- Branch routes require `company.access` and `permission:manage_branches`.
- Customer routes require `company.access`, `module:customers`, and `permission:manage_customers`.
- Inventory routes require `company.access`, `module:inventory`, and `permission:manage_inventory`.
- Every business query must filter by the authenticated user's `company_id`.
- Stock changes must create an `inventory_transactions` row.
- Branch stock must not be changed silently outside the stock transaction flow.
- Super Admin users with `company_id = null` cannot use tenant operational screens directly.

## Phase 2.5 Optional Variant Mode Workflow

- `/settings/inventory` requires `auth`, `verified`, `company.access`, and `permission:manage_settings`.
- New companies must receive a `company_inventory_settings` row with `enable_item_variants = true`.
- Existing companies without a setting must be backfilled to `enable_item_variants = true`.
- When item variants are enabled, users manage item variants separately.
- When item variants are disabled, the item form must create or update one default variant for the item.
- The default variant uses `variant_name = Default` and stays hidden from primary simple-inventory UI labels.
- Stock In must still submit and store `item_variant_id`.
- Stock In must reject non-default variant IDs when variants are disabled for the company.
- Navigation must hide the Variants link when `Company::usesItemVariants()` returns false.

## Phase 3 Sales Workflow

- Sales routes require `auth`, `verified`, `company.access`, `module:sales`, and `permission:manage_sales`.
- Every sales query must filter by the authenticated user's `company_id`.
- Sale items must store `item_variant_id` internally.
- Sale items must snapshot item name, variant name, SKU, unit price, and cost price.
- Sale item selectors must work with variant mode enabled and disabled.
- Draft and unpaid sales can be edited while they have no payments.
- Partial, paid, and void sales cannot be edited in this phase.
- Payments cannot exceed the current balance due.
- Payment recording updates `amount_paid`, `balance_due`, and sale status.
- Inventory is deducted only when a sale becomes paid.
- Paid sale stock deduction creates `inventory_transactions` with `transaction_type = sale`, negative quantity, `reference_type = Sale`, and `reference_id = sale id`.
- Paid sale stock deduction must check for existing sale inventory transactions before deducting so stock is not deducted twice.
- The Sales navigation link should render only when the sales module is enabled and the user has `manage_sales`.

## Phase 3.5 Staff & User Workflow

- `/staff` routes require `auth`, `verified`, `company.access`, and `permission:manage_users`.
- Company staff queries must filter by the authenticated user's `company_id`.
- Company Admin users cannot create, edit, or delete Super Admin users.
- Company Admin users cannot delete themselves.
- Company staff creation must set `users.company_id` to the authenticated user's company.
- Staff roles must be assigned through the existing `user_roles` pivot.
- Staff branch assignment uses `user_roles.branch_id`.
- Only one role and one branch assignment are supported per staff user for now.
- `/admin/users` routes require `auth`, `verified`, and `role:Super Admin`.
- Super Admin users can view and manage users across companies.
- Super Admin users can create platform Super Admin users with `company_id = null`.
- Non-Super Admin users created through `/admin/users` require a company.
- Branch assignment through `/admin/users` must belong to the selected company.
- The last Super Admin user cannot be deleted.
- Inactive users must be blocked from logging in.
- Staff and platform user deletes use soft deletes.

## Phase 4 Service Operations Workflow

- Service catalog and asset routes require `auth`, `verified`, `company.access`, `module:services`, and `permission:manage_services`.
- Booking management routes require `auth`, `verified`, `company.access`, `module:bookings`, and `permission:manage_bookings`.
- Job order routes require `auth`, `verified`, `company.access`, `module:job_orders`, and `permission:manage_job_orders`.
- Public booking routes use `/book/{company:slug}` and do not require authentication.
- Public booking must resolve an active or trial company by slug.
- Public booking must only show active branches, asset types, and services for the resolved company.
- Public booking submissions must create a pending booking with customer and asset snapshot fields.
- Public booking submissions must not create an active customer record.
- Booking confirmation must create or update a customer using phone/email.
- Booking confirmation must create or update a customer asset from booking snapshot fields.
- Booking confirmation must create one job order and must not duplicate job orders on repeated confirmation.
- Booking services must copy to job order services as snapshots.
- Customer assets replace hardcoded motorcycle-specific service logic.
- Job orders can have multiple technicians through `job_order_technicians`.
- Job order technician assignment must only accept active users under the same company.
- Job order services store service snapshots and optional notes/status.
- Job order items store item variant snapshots and are optional.
- Job order item stock must not be deducted until completion.
- Completing a job order must deduct inventory once through `inventory_transactions.transaction_type = job_order_usage`.
- Completing a job order must block negative stock.
- Completing a job order must create one `customer_asset_service_histories` record.
- Completing a job order linked to a booking may mark the booking completed.
- Navigation should show Asset Types, Customer Assets, Services, Service Categories, Public Booking, Bookings, and Job Orders only when the related module is enabled and the user has the required permission.
