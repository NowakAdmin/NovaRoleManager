<?php

namespace NovaRoleManager\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Permission extends SpatiePermission
{
    use UsesTenantConnection;

    /**
     * Get permissions for a specific resource
     */
    public function scopeForResource($query, string $resource)
    {
        return $query->where('resource', $resource);
    }

    /**
     * Get permissions for a specific action
     */
    public function scopeForAction($query, string $action)
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
