<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    public const TYPES = [
        'initial_stock' => 'Initial Stock',
        'stock_in' => 'Stock In',
        'manual_adjustment' => 'Manual Adjustment',
        'damage' => 'Damage',
        'return' => 'Return',
        'sale' => 'Sale',
    ];

    public const STOCK_ENTRY_TYPES = [
        'initial_stock' => 'Initial Stock',
        'stock_in' => 'Stock In',
        'manual_adjustment' => 'Manual Adjustment',
        'damage' => 'Damage',
        'return' => 'Return',
    ];

    protected $fillable = [
        'company_id',
        'branch_id',
        'item_variant_id',
        'transaction_type',
        'quantity',
        'previous_stock',
        'new_stock',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'previous_stock' => 'decimal:2',
            'new_stock' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function itemVariant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
