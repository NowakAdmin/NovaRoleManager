<?php

namespace NovaRoleManager\Traits;

use Spatie\Permission\Traits\HasRoles;

/**
 * Authorizable trait for User model
 * 
 * Extends Spatie's HasRoles trait to provide permission checking methods
 * Built on top of spatie/laravel-permission package.
 */
trait Authorizable
{
    use HasRoles;

    /**
     * Check if user is superadmin (has superadmin role)
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission($permissions): bool
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];
        
        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions($permissions): bool
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];
        
        foreach ($permissions as $permission) {
            if (!$this->hasPermissionTo($permission)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Assign a role to this user
     */
    public function assignRole($role): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        if (!$this->hasRole($role)) {
            $this->roles()->attach($role->id);
        }

        return $this;
    }

    /**
     * Remove a role from this user
     */
    public function removeRole($role): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        $this->roles()->detach($role->id);

        return $this;
    }

    /**
     * Sync roles (replace all roles with given ones)
     */
}
