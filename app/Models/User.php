<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withPivot('branch_id');
    }

    public function createdJobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class, 'created_by');
    }

    public function createdExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    public function jobOrderTechnicianAssignments(): HasMany
    {
        return $this->hasMany(JobOrderTechnician::class, 'technician_id');
    }

    public function technicianIncentives(): HasMany
    {
        return $this->hasMany(TechnicianIncentive::class, 'technician_id');
    }

    public function approvedTechnicianIncentives(): HasMany
    {
        return $this->hasMany(TechnicianIncentive::class, 'approved_by');
    }

    /**
     * @param  string|array<int, string>  $roles
     */
    public function hasRole(string|array $roles): bool
    {
        return $this->roles()->whereIn('name', (array) $roles)->exists();
    }

    /**
     * @param  string|array<int, string>  $permissionKeys
     */
    public function hasPermission(string|array $permissionKeys): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionKeys): void {
                $query->whereIn('key', (array) $permissionKeys);
            })
            ->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function canAccessModule(string $moduleKey): bool
    {
        if ($this->company_id === null && $this->isSuperAdmin()) {
            return true;
        }

        return $this->company?->hasModule($moduleKey) ?? false;
    }
}
