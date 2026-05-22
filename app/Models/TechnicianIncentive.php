<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicianIncentive extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'company_id',
        'branch_id',
        'job_order_id',
        'job_order_service_id',
        'technician_id',
        'service_id',
        'service_name_snapshot',
        'default_amount',
        'override_amount',
        'final_amount',
        'override_reason',
        'status',
        'approved_by',
        'approved_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
            'override_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
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

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function jobOrderService(): BelongsTo
    {
        return $this->belongsTo(JobOrderService::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
