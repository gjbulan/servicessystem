# Phase 4 Service Operations

## Purpose

Phase 4 adds service operations for companies that provide services while keeping the workflow flexible for motoshops, solar service providers, repair shops, and other service businesses.

This phase builds asset types, customer assets, service catalog records, public bookings, booking confirmation, job orders, multiple technician assignments, job order services/items, inventory usage on completion, and service history.

This phase does not build technician incentives, purchase orders, accounting, subscriptions, or advanced reports.

## Database Tables Added

- `asset_types`
- `customer_assets`
- `service_categories`
- `services`
- `bookings`
- `booking_services`
- `job_orders`
- `job_order_technicians`
- `job_order_services`
- `job_order_items`
- `customer_asset_service_histories`

## Models Added

- `App\Models\AssetType`
- `App\Models\CustomerAsset`
- `App\Models\ServiceCategory`
- `App\Models\Service`
- `App\Models\Booking`
- `App\Models\BookingService`
- `App\Models\JobOrder`
- `App\Models\JobOrderTechnician`
- `App\Models\JobOrderService`
- `App\Models\JobOrderItem`
- `App\Models\CustomerAssetServiceHistory`

## Controllers Added

- `App\Http\Controllers\PublicBookingController`
- `App\Http\Controllers\BookingController`
- `App\Http\Controllers\JobOrderController`
- `App\Http\Controllers\Services\AssetTypeController`
- `App\Http\Controllers\Services\CustomerAssetController`
- `App\Http\Controllers\Services\ServiceCategoryController`
- `App\Http\Controllers\Services\ServiceController`

## Public Booking

Public booking routes:

- `GET /book/{company:slug}`
- `POST /book/{company:slug}`

Public booking rules:

- The route resolves the tenant company by slug.
- The company must be active or trial.
- The company must have the `bookings` module enabled.
- The form shows only active tenant branches, asset types, and services.
- Customers do not need to register.
- A submitted booking starts as `pending`.
- Public booking stores customer, asset, and service details as snapshots.
- Public booking does not create an active `customers` record immediately.

## Booking Management

Authenticated booking routes:

- `GET /bookings`
- `GET /bookings/public-info`
- `GET /bookings/{booking}`
- `POST /bookings/{booking}/confirm`
- `POST /bookings/{booking}/cancel`
- `POST /bookings/{booking}/no-show`

Booking management rules:

- Booking queries are filtered by the authenticated user's `company_id`.
- The default booking list shows pending and confirmed bookings.
- Confirming a booking creates or updates a customer by phone/email.
- Confirming a booking creates or updates a customer asset.
- Confirming a booking creates one job order if one does not already exist.
- Requested booking services are copied to job order services as snapshots.
- Cancelling and marking no-show update only the booking status.

## Job Orders

Authenticated job order routes:

- `GET /job-orders`
- `GET /job-orders/{jobOrder}`
- `GET /job-orders/{jobOrder}/edit`
- `PATCH /job-orders/{jobOrder}`
- `GET /job-orders/{jobOrder}/assign-technicians`
- `POST /job-orders/{jobOrder}/assign-technicians`
- `POST /job-orders/{jobOrder}/complete`
- `POST /job-orders/{jobOrder}/cancel`

Job order rules:

- Job order queries are filtered by the authenticated user's `company_id`.
- A job order can have multiple technicians through `job_order_technicians`.
- Assigned technicians must be active users under the same company.
- One technician can be marked primary, but it is not required.
- Job order services store service snapshots and optional notes/status.
- Job order items store item variant snapshots, quantity, cost, and selling price.
- Items are optional.
- Stock is not deducted until the job order is completed.
- Completion writes `inventory_transactions` with `transaction_type = job_order_usage`.
- Completion checks for existing job order usage transactions so stock is not double-deducted.
- Completion creates one service history record in `customer_asset_service_histories`.

## Middleware

- Service catalog and asset routes use `module:services` and `permission:manage_services`.
- Booking management routes use `module:bookings` and `permission:manage_bookings`.
- Job order routes use `module:job_orders` and `permission:manage_job_orders`.
- All authenticated Phase 4 routes also use `auth`, `verified`, and `company.access`.

## Navigation

Navigation now shows these links only when the related module is enabled and the user has the required permission:

- Asset Types
- Customer Assets
- Service Categories
- Services
- Public Booking
- Bookings
- Job Orders

## Tenant Safety

- All authenticated Phase 4 queries are scoped by `company_id`.
- Public booking only exposes active records for the resolved company.
- Booking confirmation verifies and creates records inside the booking's company.
- Job order technician assignment only accepts active users from the same company.
- Job order inventory completion uses the job order's company, branch, and item variants.

## Files Created

- `app/Http/Controllers/PublicBookingController.php`
- `app/Http/Controllers/BookingController.php`
- `app/Http/Controllers/JobOrderController.php`
- `app/Http/Controllers/Services/AssetTypeController.php`
- `app/Http/Controllers/Services/CustomerAssetController.php`
- `app/Http/Controllers/Services/ServiceCategoryController.php`
- `app/Http/Controllers/Services/ServiceController.php`
- `app/Models/AssetType.php`
- `app/Models/CustomerAsset.php`
- `app/Models/ServiceCategory.php`
- `app/Models/Service.php`
- `app/Models/Booking.php`
- `app/Models/BookingService.php`
- `app/Models/JobOrder.php`
- `app/Models/JobOrderTechnician.php`
- `app/Models/JobOrderService.php`
- `app/Models/JobOrderItem.php`
- `app/Models/CustomerAssetServiceHistory.php`
- `database/migrations/2026_05_22_000008_create_service_operations_tables.php`
- `resources/views/bookings/*`
- `resources/views/job-orders/*`
- `resources/views/public-bookings/create.blade.php`
- `resources/views/services/*`
- `tests/Feature/ServiceOperationsTest.php`

## Files Changed

- `app/Models/Branch.php`
- `app/Models/Company.php`
- `app/Models/Customer.php`
- `app/Models/InventoryTransaction.php`
- `app/Models/ItemVariant.php`
- `app/Models/User.php`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/layouts/sidebar.blade.php`
- `routes/web.php`
- `docs/*`

## Verification

- `vendor\bin\pint` passed.
- `php artisan test --filter=ServiceOperationsTest`: 7 tests passed.
- `php artisan test`: 65 tests passed.
