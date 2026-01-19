<?php

namespace NovaRoleManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Permission extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $fillable = ['name', 'description', 'resource', 'action'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the roles that have this permission
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'nrm_role_permission',
            'permission_id',
            'role_id'
        );
    }

    /**
     * Scope to filter permissions by resource
     */
    public function scopeForResource($query, $resource)
    {
        return $query->where('resource', $resource);
    }

    /**
     * Scope to filter permissions by action
     */
    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Generate a permission name from resource and action
     */
    public static function makePermissionName($resource, $action): string
    {
        return "{$action}.{$resource}";
    }
}
