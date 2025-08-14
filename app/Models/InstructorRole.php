<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class InstructorRole extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'role_type',
        'department',
        'permissions',
        'is_active',
        'appointed_at',
        'expires_at',
    ];

    protected $casts = [
        'permissions' => 'json',
        'is_active' => 'boolean',
        'appointed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(ClassModel::class, 'instructor_id', 'user_id');
    }

    public function hasPermission(string $permission): bool
    {
        if (!$this->is_active || ($this->expires_at && $this->expires_at->isPast())) {
            return false;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions) || $this->role_type === 'department_admin';
    }

    public function isActive(): bool
    {
        return $this->is_active && (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeByRoleType($query, string $roleType)
    {
        return $query->where('role_type', $roleType);
    }

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }
}
