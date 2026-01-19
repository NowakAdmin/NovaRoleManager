<?php

namespace NovaRoleManager\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Role extends SpatieRole
{
    use UsesTenantConnection;

    /**
     * Grant permissions to this role
     */
    public function grantPermission($permissions): self
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];
        
        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $this->givePermissionTo($permission);
            } else {
                $this->givePermissionTo($permission);
            }
        }
        
        return $this;
    }

    /**
     * Revoke a permission from this role
     */
    public function revokePermission($permissions): self
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];
        
        foreach ($permissions as $permission) {
            $this->revokePermissionTo($permission);
        }
        
        return $this;
    }

    /**
     * Revoke all permissions from this role
     */
    public function revokeAllPermissions(): self
    {
        $this->permissions()->detach();
        return $this;
    }

    /**
     * Helper to create permission name in format: resource.action
     */
    public static function makePermissionName(string $resource, string $action): string
    {
        return "{$resource}.{$action}";
    }
}
