<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;

trait BelongsToTenant
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToTenant(): void
    {
        // Automatically scope all queries to the current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = Config::get('app.tenant_id');
            if ($tenantId) {
                $builder->where('tenant_id', $tenantId);
            }
        });

        // Automatically set tenant_id when creating models
        static::creating(function ($model) {
            $tenantId = Config::get('app.tenant_id');
            if ($tenantId && !$model->tenant_id) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    /**
     * Relationship to tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope query to specific tenant
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope query without tenant restrictions (for super admin use)
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Get all records for all tenants (super admin only)
     */
    public static function allTenants()
    {
        return static::withoutGlobalScope('tenant');
    }

    /**
     * Check if model belongs to current tenant
     */
    public function belongsToCurrentTenant(): bool
    {
        $currentTenantId = Config::get('app.tenant_id');
        return $currentTenantId && $this->tenant_id == $currentTenantId;
    }

    /**
     * Set tenant manually (useful for seeding or admin operations)
     */
    public function setTenant(int $tenantId): self
    {
        $this->tenant_id = $tenantId;
        return $this;
    }
}