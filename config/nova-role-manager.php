<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    | The user model to use for role and permission associations.
    */
    'user_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    | Define all resources that can have permissions assigned.
    | Format: 'resource_key' => 'Display Name'
    */
    'resources' => [
        'user' => 'User',
        'role' => 'Role',
        'permission' => 'Permission',
        // Add your application resources here
    ],

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    | Define all actions that can be performed on resources.
    */
    'actions' => [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'restore' => 'Restore',
        'force_delete' => 'Force Delete',
        'manage' => 'Manage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Nova Resources
    |--------------------------------------------------------------------------
    | Show/hide Nova resources for Role and Permission management.
    */
    'show_nova_resources' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto-seed Default Permissions
    |--------------------------------------------------------------------------
    | Whether to automatically seed default permissions on first run.
    */
    'auto_seed_permissions' => true,
];
