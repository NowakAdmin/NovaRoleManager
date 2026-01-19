<?php

namespace NovaRoleManager\Observers;

use NovaRoleManager\Models\Role;

class UserObserver
{
    /**
     * Mark the first user created in a tenant as superadmin
     */
    public function created($user): void
    {
        $userModel = config('nova-role-manager.user_model', \App\Models\User::class);

        // Check if this is the first user in the current tenant
        $userCount = $userModel::count();

        if ($userCount === 1) {
            // Get or create superadmin role
            $superAdminRole = Role::firstOrCreate(
                ['name' => 'superadmin'],
                [
                    'description' => 'Super Administrator with all permissions',
                    'is_superadmin' => true,
                ]
            );

            // Assign superadmin role to the first user
            if (method_exists($user, 'assignRole')) {
                $user->assignRole($superAdminRole);
            }
        }
    }
}
