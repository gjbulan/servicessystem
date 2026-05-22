<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOrder extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'open' => 'Open',
        'checked_in' => 'Checked In',
        'in_progress' => 'In Progress',
        'waiting_approval' => 'Waiting Approval',
        'waiting_parts' => 'Waiting Parts',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'company_id',
        'branch_id',
        'booking_id',
        'customer_id',
        'customer_asset_id',
        'job_order_number',
        'status',
        'customer_complaint',
        'inspection_notes',
        'internal_notes',
        'approval_status',
        'approval_notes',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerAsset(): BelongsTo
    {
        return $this->belongsTo(CustomerAsset::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function technicians(): HasMany
    {
        return $this->hasMany(JobOrderTechnician::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(JobOrderService::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JobOrderItem::class);
    }

    public function serviceHistory(): HasOne
    {
        return $this->hasOne(CustomerAssetServiceHistory::class);
    }
}
