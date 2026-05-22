# Phase 2.5 Optional Variant Mode

## Purpose

Phase 2.5 keeps the inventory database variant-based while allowing simple-inventory companies to avoid a separate Variants workflow in the UI.

Variant mode remains enabled by default because motoshops and specification-heavy businesses need multiple variants per item. Companies that only need simple inventory can disable item variants and manage SKU, barcode, price, and unit fields directly from the item form.

This phase does not build sales, POS, invoices, services, bookings, job orders, technician incentives, purchase orders, or accounting.

## Database Tables Added

### `company_inventory_settings`

- `id`
- `company_id` foreign key to `companies.id`, cascades on hard delete
- `enable_item_variants` boolean, default `true`
- `created_at`, `updated_at`
- unique index on `company_id`

Existing companies receive a settings record with `enable_item_variants = true` during migration. New companies receive the default setting automatically from the `Company` model boot hook.

## Models Added

- `App\Models\CompanyInventorySetting`

## Relationships Added

- `Company` has one inventory setting.
- `CompanyInventorySetting` belongs to company.
- `Item` has one default variant through `defaultVariant()`.

## Helper Methods Added

- `Company::ensureDefaultInventorySetting()`
- `Company::usesItemVariants()`

`Company::usesItemVariants()` returns `true` when the company setting is enabled or when the settings table is not available yet.

## Seeder Added

- `CompanyInventorySettingSeeder`

The seeder creates missing inventory settings for existing companies and defaults them to `enable_item_variants = true`.

## Routes Added

- `GET /settings/inventory`
- `PATCH /settings/inventory/{inventorySetting}`

Route middleware:

- `auth`
- `verified`
- `company.access`
- `permission:manage_settings`

## Controller Added

- `App\Http\Controllers\InventorySettingController`

The controller lets company users manage their own inventory setting. Super Admin users with `company_id = null` can view and update all companies when they pass the existing Super Admin permission bypass.

## View Added

- `resources/views/settings/inventory.blade.php`

The page provides an Enable Item Variants toggle:

- ON: Items can have multiple variants like size, color, or specification.
- OFF: The system hides variants from inventory forms and automatically creates one default variant for each item.

## Item Behavior

When item variants are enabled:

- Existing Phase 2 behavior remains.
- Users create item records first.
- Users create one or more variants separately.

When item variants are disabled:

- The item form shows SKU, barcode, cost price, selling price, and unit type.
- Creating an item also creates one `item_variants` row.
- Updating an item also updates the default variant fields.

Default variant values:

- `variant_name`: `Default`
- `sku`: submitted SKU
- `barcode`: submitted barcode
- `cost_price`: submitted cost price
- `selling_price`: submitted selling price
- `unit_type`: submitted unit type
- `status`: `active`

The `item_variants` table is still required. Branch stock and inventory transactions continue to reference `item_variant_id`.

## Navigation Behavior

When item variants are disabled:

- The Variants navigation link is hidden.
- Inventory users work from Items and Stock In.

When item variants are enabled:

- The Variants navigation link is visible to users with inventory access.

## Stock-In Behavior

When item variants are enabled:

- Stock In displays brand, item, variant, and SKU.

When item variants are disabled:

- Stock In displays item names and SKU.
- The submitted value is still the default `item_variant_id`.
- Direct posts to non-default variants are rejected in simple mode.

## Display Rules

When variants are disabled:

- Item lists and item details show SKU, barcode, unit, and price from the default variant.
- The UI avoids showing the `Default` variant label prominently.

When variants are enabled:

- Variant screens and stock-in labels show brand, item, variant, and SKU.

## Tenant Safety

All settings, item, variant, stock, and transaction queries remain filtered by the authenticated user's `company_id`.

Normal company users can only manage their own company inventory setting. Super Admin users can manage inventory settings for all companies through `/settings/inventory`.

## Files Created

- `app/Http/Controllers/InventorySettingController.php`
- `app/Models/CompanyInventorySetting.php`
- `database/migrations/2026_05_22_000005_create_company_inventory_settings_table.php`
- `database/seeders/CompanyInventorySettingSeeder.php`
- `resources/views/settings/inventory.blade.php`
- `tests/Feature/OptionalVariantModeTest.php`

## Files Changed

- `app/Http/Controllers/Inventory/ItemController.php`
- `app/Http/Controllers/Inventory/StockInController.php`
- `app/Models/Company.php`
- `app/Models/Item.php`
- `database/seeders/DatabaseSeeder.php`
- `resources/views/inventory/items/_form.blade.php`
- `resources/views/inventory/items/index.blade.php`
- `resources/views/inventory/items/show.blade.php`
- `resources/views/inventory/stock-in/create.blade.php`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/layouts/sidebar.blade.php`
- `routes/web.php`

## Verification

- `php artisan test --filter=OptionalVariantModeTest`: 5 tests passed.
- `php artisan test`: 44 tests passed.
