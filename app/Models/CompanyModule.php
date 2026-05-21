<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyModule extends Model
{
    public const DEFAULT_MODULES = [
        'customers' => [
            'module_name' => 'Customers',
            'description' => 'Customer records and customer-facing workflows.',
            'is_enabled' => true,
            'group' => 'Default enabled modules',
        ],
        'inventory' => [
            'module_name' => 'Inventory',
            'description' => 'Stock, item, and warehouse-related workflows.',
            'is_enabled' => true,
            'group' => 'Default enabled modules',
        ],
        'sales' => [
            'module_name' => 'Sales',
            'description' => 'Sales workflows for companies that sell products or services.',
            'is_enabled' => true,
            'group' => 'Default enabled modules',
        ],
        'invoices' => [
            'module_name' => 'Invoices',
            'description' => 'Invoice workflows and billing records.',
            'is_enabled' => true,
            'group' => 'Default enabled modules',
        ],
        'reports' => [
            'module_name' => 'Reports',
            'description' => 'Operational and business reporting.',
            'is_enabled' => true,
            'group' => 'Default enabled modules',
        ],
        'services' => [
            'module_name' => 'Services',
            'description' => 'Service catalog and service-related workflows.',
            'is_enabled' => true,
            'group' => 'Optional service modules',
        ],
        'bookings' => [
            'module_name' => 'Bookings',
            'description' => 'Appointment and booking workflows.',
            'is_enabled' => true,
            'group' => 'Optional service modules',
        ],
        'job_orders' => [
            'module_name' => 'Job Orders',
            'description' => 'Work order workflows for service delivery.',
            'is_enabled' => true,
            'group' => 'Optional service modules',
        ],
        'technician_incentives' => [
            'module_name' => 'Technician Incentives',
            'description' => 'Technician incentive and commission workflows.',
            'is_enabled' => true,
            'group' => 'Optional service modules',
        ],
        'accounting' => [
            'module_name' => 'Accounting',
            'description' => 'Advanced accounting workflows.',
            'is_enabled' => false,
            'group' => 'Advanced modules',
        ],
        'purchase_orders' => [
            'module_name' => 'Purchase Orders',
            'description' => 'Purchase order workflows for suppliers and procurement.',
            'is_enabled' => false,
            'group' => 'Advanced modules',
        ],
        'stock_transfers' => [
            'module_name' => 'Stock Transfers',
            'description' => 'Stock transfer workflows across branches or storage locations.',
            'is_enabled' => false,
            'group' => 'Advanced modules',
        ],
    ];

    protected $fillable = [
        'company_id',
        'module_key',
        'module_name',
        'description',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return array<string, array<string, bool|string|null>>
     */
    public static function definitions(): array
    {
        return self::DEFAULT_MODULES;
    }

    /**
     * @return array<string, bool|string|null>
     */
    public static function definitionFor(string $moduleKey): array
    {
        $normalizedKey = self::normalizeKey($moduleKey);

        return self::DEFAULT_MODULES[$normalizedKey] ?? [
            'module_name' => ucwords(str_replace('_', ' ', $normalizedKey)),
            'description' => null,
            'is_enabled' => true,
            'group' => 'Custom modules',
        ];
    }

    public static function normalizeKey(string $moduleKey): string
    {
        return str_replace(' ', '_', strtolower(trim($moduleKey)));
    }
}
