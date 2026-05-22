<?php

namespace NowakAdmin\NovaRoleManager\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use UsesTenantConnection;

    protected static function booted(): void
    {
        static::saving(function (self $permission): void {
            if (filled($permission->resource) && filled($permission->action)) {
                $permission->name = self::makePermissionName($permission->resource, $permission->action);
            }
        });
    }

    /**
     * Get permissions for a specific resource
     */
    public function scopeForResource(Builder $query, string $resource): Builder
    {
        return $query->where('resource', $resource);
    }

    /**
     * Get permissions for a specific action
     */
    public function scopeForAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Helper to create permission name in format: resource.action
     */
    public static function makePermissionName(string $resource, string $action): string
    {
        return "{$resource}.{$action}";
    }
}
