# GitHub Copilot / AI Agent Instructions for NowakAdmin/NovaRoleManager

## Quick context (big picture) ✅
- **Role-based access control (RBAC)** integration for Bizanti ERP
- Namespace: `NowakAdmin\NovaRoleManager`
- Provides Nova resources for managing roles, permissions, and role assignments
- Uses `spatie/laravel-permission` package as foundation for RBAC functionality
- Extends Nova with role and permission management UI
- Tenant-aware: Each tenant has its own roles and permissions
- System: Laravel 12.45.1, Nova 5.7.6, PHP 8.3+, Spatie Multitenancy

## Where to look first 🔎
- Service provider: [src/NovaRoleManagerServiceProvider.php](src/NovaRoleManagerServiceProvider.php) (registers resources, routes, migrations)
- Nova resources: [src/Nova/](src/Nova/) - `Role`, `Permission`, and related resources
- Models: [src/Models/](src/Models/) (Role, Permission models extending Spatie)
- Migrations: [database/migrations/](database/migrations/) (role/permission tables)
- Routes: [routes/api.php](routes/api.php) (role/permission API endpoints)
- Configuration: [config/nova-role-manager.php](config/nova-role-manager.php) (role setup)
- Localization: [resources/lang/](resources/lang/) (role/permission labels)

## Key concepts 🎯

### Role-based access control (RBAC)
- **Roles**: Named groups of permissions (e.g., Admin, Sales Manager, Viewer)
- **Permissions**: Individual actions (e.g., create-invoice, view-reports, delete-users)
- **Users**: Assigned to roles; inherit permissions from roles
- **Tenant-scoped**: Each tenant manages its own roles and permissions

### Spatie Permission integration
- Extends `spatie/laravel-permission` package
- Models use: `HasRoles` trait (User model), `HasPermissions` trait (roles)
- Database tables: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`
- Middleware: `role:admin` or `permission:create-invoice` for route protection

### Default roles (per tenant)
- **Admin** - Full system access (all permissions)
- **Manager** - Module-specific management (assigned permissions)
- **User** - Basic access (view, limited actions)
- Custom roles can be created per tenant

## Important conventions & patterns ⚠️

### Protecting routes with roles/permissions
```php
// In routes files (Bizanti/routes/web.php)

// Check role
Route::middleware('role:admin')->group(function () {
    // Admin-only routes
});

// Check permission
Route::middleware('permission:create-invoice')->group(function () {
    // Requires create-invoice permission
});

// Multiple permissions (all required)
Route::middleware('permission:create-invoice,approve-invoice')->group(function () {
    // Requires both permissions
});
```

### Assigning roles to users
```php
use NowakAdmin\BizantiCore\Models\User;

$user = User::find($id);

// Assign single role
$user->assignRole('admin');

// Assign multiple roles
$user->assignRole(['admin', 'manager']);

// Remove role
$user->removeRole('admin');

// Sync roles (replace all with these)
$user->syncRoles(['user', 'manager']);
```

### Checking user roles/permissions
```php
// Check role
if ($user->hasRole('admin')) { }

// Check any role
if ($user->hasAnyRole(['admin', 'manager'])) { }

// Check permission
if ($user->hasPermissionTo('create-invoice')) { }

// Check any permission
if ($user->hasAnyPermission(['create-invoice', 'view-reports'])) { }

// Direct permission check via gate
if (Gate::allows('create-invoice')) { }

// Deny action
if (Gate::denies('create-invoice')) { }
```

### Nova resource protection
```php
// In Nova resource classes
use Laravel\Nova\Actions\Action;

class MyResource extends Resource {
    public static function label() {
        return __('my-resource');
    }

    public static function authorizedToCreate(Request $request) {
        return $request->user()->hasPermissionTo('create-my-resource');
    }

    public function authorizedToUpdate(Request $request) {
        return $request->user()->hasPermissionTo('update-my-resource');
    }

    public function authorizedToDelete(Request $request) {
        return $request->user()->hasPermissionTo('delete-my-resource');
    }
}
```

### Permission naming conventions
- Use snake_case format: `create-invoice`, `view-reports`, `approve-subscription`
- Prefix with action: `create-`, `read-`, `update-`, `delete-`
- Organize by feature: `invoice-*`, `contract-*`, `product-*`

### Multi-tenancy with roles
- Roles are **tenant-scoped** (each tenant has independent role setup)
- When switching tenants, only that tenant's roles are visible
- Permission checking automatically scoped to current tenant
- Role migrations published to tenant folder

## Data flow architecture 🔄

```
User logs in
    ↓
Auth system loads User with roles
    ↓ (via HasRoles trait)
User makes request
    ↓
Middleware checks: role:admin or permission:create-invoice
    ↓
Check user.roles and user.permissions (from database)
    ↓ (permission/role found)
Request allowed, route controller executes
    ↓
Nova resource authorization methods called
    ↓
Gate/Policy checks permission again
    ↓
Action allowed or denied based on user permissions
```

## Integration points 📡
- **Laravel Nova**: Role/Permission resources registered as Nova resources
- **Spatie Permission**: Underlying RBAC engine (`spatie/laravel-permission`)
- **Spatie Multitenancy**: Roles are tenant-scoped
- **BizantiCore**: User model uses `HasRoles` trait from this package
- **All Bizanti modules**: Can define module-specific permissions

## Common pitfalls 🚨
- **Forgetting User trait**: User model must use `HasRoles` trait from Spatie
- **Tenant isolation**: Ensure role checking happens WITHIN current tenant context
- **Permission string format**: Use lowercase with hyphens, not camelCase or underscores
- **Caching issues**: Role/permission caching may show stale data; clear cache after changes
- **Missing migrations**: Run `php artisan tenants:artisan "migrate"` to create role tables in all tenants
- **Nova resource protection**: Remember to add authorization methods to all CRUD resources
- **Gate vs Policy**: Use Gate for simple checks, Policy for model-based authorization

## Typical tasks (quick reference) 📝

### Setting up roles for a new feature (example: Invoices)
1. In Nova (or via code), create permissions:
   - `view-invoices`
   - `create-invoices`
   - `update-invoices`
   - `delete-invoices`
   - `approve-invoices`

2. Create role `Invoice Manager` and assign permissions:
   ```php
   $role = Role::create(['name' => 'invoice-manager']);
   $role->givePermissionTo(['view-invoices', 'create-invoices', 'update-invoices', 'approve-invoices']);
   ```

3. Create route group with permission check:
   ```php
   Route::middleware('permission:view-invoices')->group(function () {
       Route::get('/invoices', InvoiceController@index);
   });
   ```

4. Protect Nova resource:
   ```php
   public static function authorizedToCreate(Request $request) {
       return $request->user()->hasPermissionTo('create-invoices');
   }
   ```

5. Assign users to role:
   ```php
   $user->assignRole('invoice-manager');
   ```

### Creating custom permission groups
1. Define permissions in config or Nova UI:
   - Group: "Invoices" → permissions: view, create, update, delete, approve
   - Group: "Reports" → permissions: view, export, email
   - Group: "Users" → permissions: create, update, delete, assign-roles

2. In NovaRoleManager UI, manage roles by assigning these grouped permissions

3. Code automatically sees all defined permissions

### Assigning role to user via code
```php
use NowakAdmin\BizantiCore\Models\User;

$user = User::findOrFail($id);
$user->assignRole('admin');

// Or via Nova resource — user goes to Roles tab and selects role
```

### Checking permissions in controller
```php
class InvoiceController extends Controller {
    public function index(Request $request) {
        $request->user()->hasPermissionTo('view-invoices');
        // Show invoices
    }

    public function create(Request $request) {
        $request->user()->hasPermissionTo('create-invoices');
        // Return create form
    }
}
```

### Scoping Nova resources by permission
```php
class InvoiceResource extends Resource {
    public static function indexQuery(NovaRequest $request, $query) {
        // User can only see invoices if they have permission
        if (!$request->user()->hasPermissionTo('view-all-invoices')) {
            return $query->where('user_id', $request->user()->id);
        }
        return $query;
    }
}
```

## API Reference 📚

### User role methods
- `$user->assignRole($role)` - Assign role
- `$user->removeRole($role)` - Remove role
- `$user->syncRoles($roles)` - Replace roles
- `$user->hasRole($role)` - Check single role
- `$user->hasAnyRole($roles)` - Check any of multiple roles
- `$user->roles` - Get all roles

### User permission methods
- `$user->givePermissionTo($permission)` - Grant permission
- `$user->revokePermissionTo($permission)` - Revoke permission
- `$user->hasPermissionTo($permission)` - Check permission
- `$user->hasAnyPermission($permissions)` - Check any of multiple
- `$user->permissions` - Get all permissions

### Role methods
- `Role::create(['name' => 'admin'])` - Create role
- `$role->givePermissionTo($permission)` - Assign permission to role
- `$role->revokePermissionTo($permission)` - Remove permission from role
- `$role->permissions` - Get all permissions in role

### Middleware
- `middleware('role:admin')` - Check specific role
- `middleware('permission:create-invoice')` - Check specific permission
- `middleware('role_or_permission:admin,create-invoice')` - Check role OR permission

## Testing & debugging 🧪
- List all permissions: `Permission::all()`
- List all roles: `Role::all()`
- Check user roles: `$user->roles`
- Check user permissions: `$user->permissions`
- Verify role has permission: `$role->permissions()->pluck('name')`
- Clear permission cache after manual DB updates: `php artisan cache:forget spatie.permission.cache`
- Test middleware in Tinker: `auth()->user()->hasPermissionTo('create-invoice')`

## Questions to clarify?
- Need granular permissions per tenant? Roles/permissions automatically tenant-scoped
- Want to sync permissions across tenants? Create custom command
- Need to log permission changes? Add observer to Role/Permission models
- Want hierarchical roles (Manager > User)? Extend role structure with parent_id field

