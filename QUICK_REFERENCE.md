# NovaRoleManager - Quick Reference Card

Print this or bookmark for quick lookups during integration.

---

## Installation One-Liner

```bash
# 1. Add to BizantiMaintenance
composer require nowakadmin/nova-role-manager:dev-main

# 2. Publish and migrate (in Bizanti main app)
php artisan vendor:publish --provider="NovaRoleManager\Providers\NovaRoleManagerServiceProvider"
php artisan migrate
```

---

## Key Models

### Role
```php
$role = Role::create(['name' => 'Editor', 'is_superadmin' => false]);
$role->grantPermission(['post.view', 'post.create']);
$role->revokePermission('post.delete');
```

### Permission
```php
Permission::create(['name' => 'post.create', 'resource' => 'post', 'action' => 'create']);
$permissions = Permission::forResource('post')->get();
```

### User (via Authorizable trait)
```php
use NovaRoleManager\Traits\Authorizable;

class User extends Authenticatable {
    use Authorizable;
}
```

---

## User Permission Methods

| Method | Usage | Returns |
|--------|-------|---------|
| `hasRole($role)` | `$user->hasRole('Editor')` | bool |
| `hasPermission($perm)` | `$user->hasPermission('post.create')` | bool |
| `hasAnyPermission($perms)` | `$user->hasAnyPermission(['post.create', 'post.update'])` | bool |
| `hasAllPermissions($perms)` | `$user->hasAllPermissions(['post.create', 'post.view'])` | bool |
| `isSuperAdmin()` | `$user->isSuperAdmin()` | bool |
| `assignRole($role)` | `$user->assignRole('Editor')` | User |
| `removeRole($role)` | `$user->removeRole('Editor')` | User |
| `syncRoles($roles)` | `$user->syncRoles(['Editor', 'Viewer'])` | User |

---

## Creating Custom Policy

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
        return 'post'; // Used to check 'post.view', 'post.create', etc.
    }

    public function view(User $user, Post $post)
    {
        if (parent::view($user, $post) === false) return false;
        return $post->is_published || $post->author_id === $user->id;
    }
}
```

---

## Register Policy

In `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    Post::class => PostPolicy::class,
];
```

---

## Use Policy in Controller

```php
public function show(Post $post)
{
    $this->authorize('view', $post);
    return view('post.show', compact('post'));
}
```

---

## Permission Naming Convention

Use dot notation: `{resource}.{action}`

**Examples:**
- `post.view` → View posts
- `post.create` → Create posts
- `post.update` → Update posts
- `post.delete` → Delete posts
- `event.assign` → Assign events
- `task.schedule` → Schedule tasks

---

## Configuration

File: `config/nova-role-manager.php`

```php
return [
    'user_model' => \App\Models\User::class,
    'resources' => ['post' => 'Post', 'event' => 'Event'],
    'actions' => ['view' => 'View', 'create' => 'Create'],
];
```

---

## Nova Admin UI

**Access Roles/Permissions in Nova:**
1. Navigate to Nova dashboard
2. Click "Roles" (sidebar)
3. Create/edit roles and grant permissions
4. Assign roles to users

---

## Seeding Initial Data

```php
// database/seeders/RolePermissionSeeder.php

use NovaRoleManager\Models\Role;
use NovaRoleManager\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        Permission::firstOrCreate(
            ['name' => 'post.create'],
            ['resource' => 'post', 'action' => 'create']
        );

        // Create role
        $editor = Role::firstOrCreate(['name' => 'Editor']);
        
        // Grant permissions
        $editor->grantPermission('post.create');
    }
}
```

---

## Multi-Tenancy

All models automatically tenant-scoped:

```php
// Roles are per-tenant
$tenantA_roles = Role::all(); // Only TenantA roles

// Switch tenant
auth()->setTenant($tenantB);
$tenantB_roles = Role::all(); // Only TenantB roles
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Trait not found | Ensure `use Authorizable` in User model |
| Nova resources missing | Run `php artisan nova:publish` |
| Permissions not working | Check user has role via Nova UI |
| Policy always allows | Override `before()` correctly in policy |
| Multi-tenant mixing | Ensure `UsesTenantConnection` on models |

---

## Files to Remember

| File | Purpose |
|------|---------|
| `config/nova-role-manager.php` | Configuration |
| `app/Models/User.php` | Add Authorizable trait |
| `app/Providers/AuthServiceProvider.php` | Register policies |
| `app/Policies/*.php` | Custom authorization |
| `database/seeders/*.php` | Initial roles/permissions |

---

## Integration Checklist

- [ ] Installed via Composer
- [ ] Migrated database
- [ ] User model has Authorizable trait
- [ ] Policies registered in AuthServiceProvider
- [ ] Custom policies created for resources
- [ ] Roles/permissions seeded
- [ ] Nova UI verified
- [ ] Permission checks working
- [ ] Policy authorization working

---

## Documentation Links

- **Full API**: README.md
- **Detailed Setup**: SETUP_GUIDE.md
- **Quick Start**: QUICK_START.md
- **Integration**: NOVA_ROLE_MANAGER_INTEGRATION.md (in main app)

---

## Version Info

- **Package Version**: 1.0.0
- **Laravel**: 12+
- **Nova**: 5+
- **PHP**: 8.2+

---

## Emergency Support

**If something breaks:**

1. Run `php artisan vendor:publish --provider="NovaRoleManager\Providers\NovaRoleManagerServiceProvider"`
2. Clear cache: `php artisan optimize:clear`
3. Check migrations: `php artisan migrate:status`
4. Verify User model has trait
5. Check config file exists

---

## Common Tasks

### Create Role
```php
Role::create(['name' => 'Technician', 'is_superadmin' => false]);
```

### Assign Role to User
```php
$user->assignRole('Technician');
```

### Grant Permission to Role
```php
Role::where('name', 'Technician')->first()->grantPermission('task.create');
```

### Check Permission in View
```php
@if(auth()->user()->hasPermission('task.create'))
    <a href="/tasks/create">Create Task</a>
@endif
```

### Check Permission in Gate
```php
Gate::define('create-task', fn($user) => $user->hasPermission('task.create'));

@can('create-task')
    <!-- Show content -->
@endcan
```

---

**Last Updated**: 2026-01-19  
**Status**: Production Ready  
**Keep This Handy!**
