<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CompanyModule::class);
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
