<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'status',
    ];

    protected static function booted(): void
    {
        static::created(function (Company $company): void {
            if (Schema::hasTable('company_modules')) {
                $company->ensureDefaultModules();
            }

            if (Schema::hasTable('company_inventory_settings')) {
                $company->ensureDefaultInventorySetting();
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function assetTypes(): HasMany
    {
        return $this->hasMany(AssetType::class);
    }

    public function customerAssets(): HasMany
    {
        return $this->hasMany(CustomerAsset::class);
    }

    public function itemCategories(): HasMany
    {
        return $this->hasMany(ItemCategory::class);
    }

    public function itemBrands(): HasMany
    {
        return $this->hasMany(ItemBrand::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function itemVariants(): HasMany
    {
        return $this->hasMany(ItemVariant::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    public function customerAssetServiceHistories(): HasMany
    {
        return $this->hasMany(CustomerAssetServiceHistory::class);
    }

    public function technicianIncentives(): HasMany
    {
        return $this->hasMany(TechnicianIncentive::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CompanyModule::class);
    }

    public function inventorySetting(): HasOne
    {
        return $this->hasOne(CompanyInventorySetting::class);
    }

    public function hasModule(string $moduleKey): bool
    {
        return $this->modules()
            ->where('module_key', CompanyModule::normalizeKey($moduleKey))
            ->where('is_enabled', true)
            ->exists();
    }

    public function enableModule(string $moduleKey): CompanyModule
    {
        return $this->setModuleEnabled($moduleKey, true);
    }

    public function disableModule(string $moduleKey): CompanyModule
    {
        return $this->setModuleEnabled($moduleKey, false);
    }

    public function ensureDefaultModules(): void
    {
        foreach (CompanyModule::definitions() as $moduleKey => $definition) {
            $module = $this->modules()->firstOrNew([
                'module_key' => $moduleKey,
            ]);

            $module->module_name = $definition['module_name'];
            $module->description = $definition['description'];

            if (! $module->exists) {
                $module->is_enabled = $definition['is_enabled'];
            }

            $module->save();
        }
    }

    public function ensureDefaultInventorySetting(): CompanyInventorySetting
    {
        return $this->inventorySetting()->firstOrCreate([
            'company_id' => $this->id,
        ], [
            'enable_item_variants' => true,
        ]);
    }

    public function usesItemVariants(): bool
    {
        if (! Schema::hasTable('company_inventory_settings')) {
            return true;
        }

        return $this->ensureDefaultInventorySetting()->enable_item_variants;
    }

    private function setModuleEnabled(string $moduleKey, bool $isEnabled): CompanyModule
    {
        $normalizedKey = CompanyModule::normalizeKey($moduleKey);
        $definition = CompanyModule::definitionFor($normalizedKey);

        return $this->modules()->updateOrCreate(
            ['module_key' => $normalizedKey],
            [
                'module_name' => $definition['module_name'],
                'description' => $definition['description'],
                'is_enabled' => $isEnabled,
            ],
        );
    }
}
