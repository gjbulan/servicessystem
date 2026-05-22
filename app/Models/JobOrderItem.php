<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderItem extends Model
{
    protected $fillable = [
        'job_order_id',
        'item_variant_id',
        'item_name_snapshot',
        'variant_name_snapshot',
        'sku_snapshot',
        'quantity',
        'cost_price_snapshot',
        'selling_price_snapshot',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'cost_price_snapshot' => 'decimal:2',
            'selling_price_snapshot' => 'decimal:2',
        ];
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function itemVariant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class);
    }
}
