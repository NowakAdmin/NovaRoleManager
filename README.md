# Nova Role Manager

A complete, reusable role-based access control (RBAC) system for Laravel Nova with multi-tenancy support, policies, and permissions.

Built on top of **[spatie/laravel-permission](https://github.com/spatie/laravel-permission)** with Nova admin UI layer and multi-tenancy integration.

## Features

- 🔐 **Role-Based Access Control (RBAC)** - Manage user roles and permissions via spatie/laravel-permission
- 🏢 **Multi-Tenancy Support** - Full tenant isolation using Spatie Multitenancy
- 📋 **Nova Integration** - Manage roles and permissions directly from Nova admin panel
- 🔑 **Policies** - Built-in Laravel policies for model authorization
- 🎯 **Flexible Permissions** - Resource-based permission system (view, create, update, delete, manage)
- 🔄 **Trait-Based** - Easy integration with existing User model via Authorizable trait
- ⚡ **Industry Standard** - Built on battle-tested spatie/laravel-permission package
- 🌍 **Multi-Language** - English and Polish translations included

## Installation

### 1. Install via Composer

```bash
composer require nowakadmin/nova-role-manager
```

### 2. Add Authorizable Trait to User Model

In your `app/Models/User.php`:

```php
use NovaRoleManager\Traits\Authorizable;
class User extends Authenticatable
{
    use Authorizable; // Add this
    // ... rest of model
}
```

### 3. Publish Files

```bash
# Publish migrations
php artisan vendor:publish --provider="NovaRoleManager\Providers\NovaRoleManagerServiceProvider" --tag=migrations

# Publish config
php artisan vendor:publish --provider="NovaRoleManager\Providers\NovaRoleManagerServiceProvider" --tag=config

# Publish translations
php artisan vendor:publish --provider="NovaRoleManager\Providers\NovaRoleManagerServiceProvider" --tag=translations
```

### 4. Run Migrations

**For multi-tenant projects:**
```bash
php artisan tenants:artisan "migrate --path=database/migrations/tenant --database=tenant"
```

**For single-tenant projects:**
```bash
php artisan migrate
```

### 5. Register Policies (Optional but Recommended)

In `app/Providers/AuthServiceProvider.php`:

```php
use Illuminate\Support\Facades\Gate;
use NovaRoleManager\Policies\BasePolicy;

protected $policies = [
    // Your models here
    YourModel::class => YourPolicy::class, // extends BasePolicy
];

public function boot(): void
{
    $this->registerPolicies();

    Gate::define('is-superadmin', fn($user) => $user->isSuperAdmin());
    Gate::define('manage-roles', fn($user) => $user->hasPermission('manage.role'));
}
```

## Configuration

Edit `config/nova-role-manager.php`:

```php
return [
    'user_model' => \App\Models\User::class,
    
    'resources' => [
        'user' => 'User',
        'role' => 'Role',
        'permission' => 'Permission',
        // Add your application resources
    ],
    
    'actions' => [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'manage' => 'Manage',
    ],
];
```

## Usage

### User Methods

```php
$user = auth()->user();

// Check roles
$user->hasRole('admin');
$user->isSuperAdmin();

// Check permissions
$user->hasPermission('view.user');
$user->hasPermission('create.role');
$user->hasAnyPermission(['view.user', 'view.role']);
$user->hasAllPermissions(['create.user', 'update.user']);

// Assign/Remove roles
$user->assignRole('admin');
$user->assignRole($roleModel);
$user->removeRole('admin');
$user->syncRoles(['admin', 'moderator']);
```

### Role Methods

```php
$role = Role::first();

// Check/grant/revoke permissions
$role->hasPermission('view.user');
$role->grantPermission('create.user');
$role->revokePermission('delete.user');
$role->revokeAllPermissions();

// Access relationships
$role->permissions;
$role->users;
```

### Nova Authorization

Use `canSee()` in Nova resources:

```php
public function fields(NovaRequest $request)
{
    return [
        // Only visible to superadmin
        Boolean::make('is_superadmin')
            ->canSee(fn() => auth()->user()->isSuperAdmin()),
        
        // Only if user has permission
        Text::make('Sensitive Field')
            ->canSee(fn() => auth()->user()->hasPermission('manage.sensitive')),
    ];
}
```

### Creating Custom Policies

Create a policy extending `BasePolicy`:

```php
namespace App\Policies;

use NovaRoleManager\Policies\BasePolicy;

class ArticlePolicy extends BasePolicy
{
    protected function getResourceName(): string
    {
        return 'article';
    }
    
    // Optional: Override specific methods
    public function update($user, Article $article)
    {
        // Custom logic
        return $user->id === $article->author_id 
            && parent::update($user, $article);
    }
}
```

Register in `AuthServiceProvider`:

```php
protected $policies = [
    Article::class => ArticlePolicy::class,
];
```

## Permission Format

Permissions follow a `action.resource` naming convention:

- `view.user` - View users
- `create.user` - Create users
- `update.user` - Update users
- `delete.user` - Delete users
- `manage.role` - Manage roles
- `manage.permission` - Manage permissions

## Default Roles

The package creates these default roles (optional via configuration):

- **superadmin** - Full access to everything
- **manager** - Can view, create, update, delete (except manage)
- **technician** - Limited access to specific resources
- **viewer** - Read-only access

## Multi-Tenancy

The package is fully compatible with Spatie Multitenancy:

```php
// All models use tenant connection automatically
$role = Role::create(...); // Scoped to current tenant
$permission = Permission::create(...); // Scoped to current tenant

// First user in each tenant automatically becomes superadmin
```

## Seeding Permissions

Create a seeder to populate permissions:

```php
use NovaRoleManager\Models\Permission;
use NovaRoleManager\Models\Role;

public function run()
{
    // Create permissions
    Permission::firstOrCreate(
        ['name' => 'view.article'],
        ['resource' => 'article', 'action' => 'view', 'description' => 'View articles']
    );
    
    // Grant to role
    $role = Role::firstOrCreate(['name' => 'editor']);
    $role->grantPermission('view.article');
}
```

## API Integration

Check permissions in your API:

```php
class ArticleController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Article::class);
        
        // Or manually:
        if (!auth()->user()->hasPermission('create.article')) {
            abort(403);
        }
        
        // ... create article
    }
}
```

## Testing

```php
public function testUserCanViewArticles()
{
    $user = User::factory()->create();
    $permission = Permission::firstOrCreate(
        ['name' => 'view.article'],
        ['resource' => 'article', 'action' => 'view']
    );
    
    $user->assignRole(
        Role::firstOrCreate(
            ['name' => 'viewer'],
            ['is_superadmin' => false]
        )
    );
    
    // Grant permission
    $user->roles->first()->grantPermission($permission);
    
    $this->assertTrue($user->hasPermission('view.article'));
}
```

## Database Schema

### Roles Table (`nrm_roles`)
- `id` - Primary key
- `tenant_id` - Tenant identifier (multi-tenancy)
- `name` - Unique role name
- `description` - Role description
- `is_superadmin` - Superadmin flag
- `created_at`, `updated_at`

### Permissions Table (`nrm_permissions`)
- `id` - Primary key
- `tenant_id` - Tenant identifier
- `name` - Unique permission name
- `resource` - Resource type (user, role, article, etc.)
- `action` - Action (view, create, update, delete, manage)
- `description` - Permission description
- `created_at`, `updated_at`

### Pivot Tables
- `nrm_role_permission` - Maps roles to permissions
- `nrm_user_role` - Maps users to roles

## Events & Observers

The package includes:

- `UserObserver` - Automatically assigns superadmin role to first user in tenant

## Troubleshooting

### First user not becoming superadmin
- Ensure `Authorizable` trait is added to User model
- Check that `UsesTenantConnection` is on User model for multi-tenancy
- Run migrations for the tenant

### Permissions not working
- Verify policies are registered in `AuthServiceProvider`
- Check permission names follow `action.resource` format
- Ensure user has required role before checking permission

### Nova resources not appearing
- Publish package with `--tag=config` and `--tag=translations`
- Clear Nova cache: `php artisan nova:publish && php artisan optimize:clear`
- Check user has `manage.role` and `manage.permission` permissions

## License

MIT License. See LICENSE file for details.

## Support

For issues and questions, please open an issue on GitHub:
https://github.com/NowakAdmin/NovaRoleManager

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
