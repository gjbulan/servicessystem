<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyInventorySetting extends Model
{
    protected $fillable = [
        'company_id',
        'enable_item_variants',
    ];

    protected function casts(): array
    {
        return [
            'enable_item_variants' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
