# Nova Role Manager - Quick Setup Guide

## Installation (5 minutes)

### Step 1: Install Package
```bash
composer require nowakadmin/nova-role-manager
```

### Step 2: Add Trait to User Model
File: `app/Models/User.php`
```php
use NovaRoleManager\Traits\Authorizable;

class User extends Authenticatable
{
    use Authorizable; // ← Add this line
    // ... rest
}
```

### Step 3: Publish and Migrate
```bash
# Publish package files
php artisan vendor:publish --provider="NovaRoleManager\Providers\NovaRoleManagerServiceProvider"

# Run migrations
php artisan migrate

# For multi-tenant:
php artisan tenants:artisan "migrate --path=database/migrations/tenant"
```

### Step 4: (Optional) Register Policies
File: `app/Providers/AuthServiceProvider.php`
```php
use NovaRoleManager\Policies\BasePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        YourModel::class => YourPolicy::class, // extends BasePolicy
    ];
}
```

## That's It! 🎉

Now you have:
- ✅ Role management in Nova
- ✅ Permission management in Nova
- ✅ First user as superadmin automatically
- ✅ Full authorization support

## Quick Start Usage

### Check User Permissions
```php
$user = auth()->user();

$user->hasRole('admin');
$user->isSuperAdmin();
$user->hasPermission('view.articles');
```

### Assign Roles
```php
$user->assignRole('admin');
$user->assignRole('editor', 'viewer');
```

### Protect Nova Resources
```php
use Laravel\Nova\Fields\Text;

public function fields(NovaRequest $request)
{
    return [
        Text::make('Sensitive Data')
            ->canSee(fn() => auth()->user()->hasPermission('manage.sensitive')),
    ];
}
```

### Protect Controllers
```php
public function destroy(Article $article)
{
    $this->authorize('delete', $article); // Uses policy
    
    $article->delete();
}
```

## Configuration

Edit `config/nova-role-manager.php` to customize:
- User model
- Resources (for permissions)
- Actions (view, create, update, delete, manage)

## For More Info

See [README.md](README.md) for complete documentation.
