# NovaRoleManager - Quick Start

A comprehensive, reusable Role-Based Access Control (RBAC) system for Laravel Nova applications.

## Installation

```bash
composer require nowakadmin/nova-role-manager
```

## Publish Package Files

```bash
php artisan vendor:publish --provider="NovaRoleManager\Providers\NovaRoleManagerServiceProvider"
```

This publishes:
- Migrations → `database/migrations/`
- Config → `config/nova-role-manager.php`
- Translations → `resources/lang/nova-role-manager/`

## Run Migrations

```bash
# Regular Laravel app
php artisan migrate

# Multi-tenant app (Spatie Multitenancy)
php artisan tenants:artisan "migrate"
```

## Add Trait to User Model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use NovaRoleManager\Traits\Authorizable;

class User extends Authenticatable
{
    use Authorizable;
    
    // ... rest of User model
}
```

## Create Roles

```php
use NovaRoleManager\Models\Role;

// In a seeder or command
$adminRole = Role::create([
    'name' => 'Administrator',
    'description' => 'Full system access',
    'is_superadmin' => true,
]);

$editorRole = Role::create([
    'name' => 'Editor',
    'description' => 'Can create and edit content',
    'is_superadmin' => false,
]);
```

## Create Permissions

```php
use NovaRoleManager\Models\Permission;

// Create individual permissions
Permission::create([
    'name' => 'view.post',
    'description' => 'View posts',
    'resource' => 'post',
    'action' => 'view',
]);

// Or use helper
Permission::firstOrCreate(
    ['name' => Permission::makePermissionName('post', 'create')],
    ['resource' => 'post', 'action' => 'create']
);
```

## Assign Roles to Users

```php
$user = User::find(1);

// Assign single role
$user->assignRole('Editor');

// Assign multiple roles
$user->assignRole(['Editor', 'Moderator']);

// Sync roles (replaces existing)
$user->syncRoles(['Editor', 'Viewer']);
```

## Grant Permissions to Roles

```php
$editorRole = Role::where('name', 'Editor')->first();

// Grant individual permission
$editorRole->grantPermission('post.create');

// Grant multiple permissions
$editorRole->grantPermission(['post.view', 'post.create', 'post.update']);

// Via relationship
$editorRole->permissions()->sync(['post.view', 'post.create']);
```

## Check Permissions in Code

```php
$user = auth()->user();

// Check single permission
if ($user->hasPermission('post.create')) {
    // User can create posts
}

// Check multiple (any match)
if ($user->hasAnyPermission(['post.create', 'post.update'])) {
    // User can create or update posts
}

// Check multiple (all required)
if ($user->hasAllPermissions(['post.create', 'post.update'])) {
    // User can create and update posts
}

// Check superadmin
if ($user->isSuperAdmin()) {
    // Unrestricted access
}

// Check role
if ($user->hasRole('Editor')) {
    // User is an editor
}
```

## Create Policies

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Post;
use NovaRoleManager\Policies\BasePolicy;

class PostPolicy extends BasePolicy
{
    protected function getResourceName(): string
    {
        return 'post';
    }

    public function view(User $user, Post $post)
    {
        if (parent::view($user, $post) === false) {
            return false; // Superadmin denied
        }

        return $post->is_published || $post->author_id === $user->id;
    }

    public function update(User $user, Post $post)
    {
        if (parent::update($user, $post) === false) {
            return false;
        }

        return $post->author_id === $user->id || $user->hasPermission('post.update');
    }
}
```

## Register Policies in AuthServiceProvider

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Policies\PostPolicy;
use App\Models\Post;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Post::class => PostPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('is-superadmin', fn($user) => $user->isSuperAdmin());
    }
}
```

## Use Policies in Controllers

```php
public function show(Post $post)
{
    $this->authorize('view', $post);
    
    return view('post.show', ['post' => $post]);
}

public function update(Request $request, Post $post)
{
    $this->authorize('update', $post);
    
    // Update post...
}
```

## Configure Package

Edit `config/nova-role-manager.php`:

```php
return [
    // User model to check permissions on
    'user_model' => \App\Models\User::class,

    // Map resource names to display labels
    'resources' => [
        'user' => 'User',
        'post' => 'Post',
        'comment' => 'Comment',
    ],

    // Map action names to display labels
    'actions' => [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'restore' => 'Restore',
        'manage' => 'Manage',
    ],
];
```

## Nova Admin UI

The package provides Nova resources for managing roles and permissions:

1. **Navigate** to Nova Dashboard
2. **Click** "Roles" or "Permissions" in sidebar
3. **Create/Edit** roles and permissions
4. **Assign** roles to users via User resource
5. **Grant** permissions to roles

## Multi-Tenancy (Spatie Multitenancy)

All models automatically use the tenant connection:

```php
// When you switch tenant, roles/permissions are tenant-scoped
$user->assignRole('Editor'); // Only for current tenant

// In another tenant
$user->hasRole('Editor'); // Different set of roles/permissions
```

## First User Auto-Assignment

When using the UserObserver (included), the first user created in each tenant automatically becomes a superadmin:

```php
$firstUser = User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
]);

// Automatically assigned to 'superadmin' role with is_superadmin=true
$firstUser->isSuperAdmin(); // true
```

To disable this, don't register the observer in your service provider.

## Advanced: Custom Permission Names

Use the Permission model's helper method:

```php
// Generate permission name format: resource.action
$permissionName = Permission::makePermissionName('post', 'create');
// Result: 'post.create'

// Use in code
if ($user->hasPermission(Permission::makePermissionName('post', 'create'))) {
    // ...
}
```

## Troubleshooting

**Issue**: Superadmin role not auto-created
- Solution: Run seeders after migration

**Issue**: Permissions not checking correctly
- Solution: Ensure User model uses `Authorizable` trait

**Issue**: Nova resources not appearing
- Solution: Clear Nova cache: `php artisan nova:publish && php artisan optimize:clear`

**Issue**: Multi-tenant roles crossing over
- Solution: Verify all models use `UsesTenantConnection` trait

## What's Next?

1. Check [SETUP_GUIDE.md](SETUP_GUIDE.md) for detailed setup
2. Read [README.md](README.md) for API documentation
3. See example implementations in test projects

## Support

- GitHub Issues: https://github.com/NowakAdmin/NovaRoleManager/issues
- Documentation: https://github.com/NowakAdmin/NovaRoleManager

---

**Version**: 1.0.0  
**License**: MIT  
**Maintainer**: NowakAdmin
