<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'service_category_id',
        'name',
        'description',
        'default_price',
        'estimated_duration_minutes',
        'default_incentive_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'default_incentive_amount' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    public function jobOrderServices(): HasMany
    {
        return $this->hasMany(JobOrderService::class);
    }

    public function technicianIncentives(): HasMany
    {
        return $this->hasMany(TechnicianIncentive::class);
    }
}
