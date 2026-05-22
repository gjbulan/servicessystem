<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'no_show' => 'No Show',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'company_id',
        'branch_id',
        'booking_reference',
        'customer_id',
        'customer_asset_id',
        'customer_name',
        'phone',
        'email',
        'asset_type_name',
        'asset_details_json',
        'preferred_datetime',
        'issue_description',
        'lead_source',
        'status',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'asset_details_json' => 'array',
            'preferred_datetime' => 'datetime',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerAsset(): BelongsTo
    {
        return $this->belongsTo(CustomerAsset::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    public function jobOrder(): HasOne
    {
        return $this->hasOne(JobOrder::class);
    }
}
