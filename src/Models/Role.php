<?php

namespace NovaRoleManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Role extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $fillable = ['name', 'description', 'is_superadmin'];

    protected $casts = [
        'is_superadmin' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the permissions for this role
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'nrm_role_permission',
            'role_id',
            'permission_id'
        );
    }

    /**
     * Get the users with this role
     */
    public function users()
    {
        return $this->belongsToMany(
            config('nova-role-manager.user_model', \App\Models\User::class),
            'nrm_user_role',
            'role_id',
            'user_id'
        );
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission($permission): bool
    {
        if ($this->is_superadmin) {
            return true;
        }

        if (is_string($permission)) {
            return $this->permissions()->where('name', $permission)->exists();
        }

        return $this->permissions()->where('name', $permission->name)->exists();
    }

    /**
     * Grant a permission to this role
     */
    public function grantPermission($permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }

        if (!$this->hasPermission($permission)) {
            $this->permissions()->attach($permission->id);
        }
    }

    /**
     * Revoke a permission from this role
     */
    public function revokePermission($permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission->id);
    }

    /**
     * Revoke all permissions
     */
    public function revokeAllPermissions(): void
    {
        $this->permissions()->detach();
    }
}
