<?php

namespace NovaRoleManager\Traits;

use NovaRoleManager\Models\Permission;
use NovaRoleManager\Models\Role;

trait Authorizable
{
    /**
     * Get the roles associated with this user
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'nrm_user_role',
            'user_id',
            'role_id'
        );
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles()->where('name', $role)->exists();
        }

        return $this->roles()->where('id', $role->id)->exists();
    }

    /**
     * Check if user is a superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->roles()->where('is_superadmin', true)->exists();
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission($permission): bool
    {
        // Superadmin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (is_string($permission)) {
            return $this->roles()
                ->whereHas('permissions', function ($query) use ($permission) {
                    $query->where('name', $permission);
                })
                ->exists();
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('id', $permission->id);
            })
            ->exists();
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('name', $permissions);
            })
            ->exists();
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
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
    public function syncRoles($roles): self
    {
        $roleIds = collect($roles)->map(function ($role) {
            return is_string($role) ? Role::where('name', $role)->firstOrFail()->id : $role->id;
        })->toArray();

        $this->roles()->sync($roleIds);

        return $this;
    }
}
