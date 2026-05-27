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
                $permission->name = self::makePermissionName($permission->action, $permission->resource);
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
     * Helper to create permission name in format: action.resource
     */
    public static function makePermissionName(string $action, string $resource): string
    {
        return "{$action}.{$resource}";
    }
}
