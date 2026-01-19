<?php

namespace NovaRoleManager\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use NovaRoleManager\Models\Role;
use NovaRoleManager\Observers\UserObserver;

class NovaRoleManagerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/nova-role-manager.php',
            'nova-role-manager'
        );
    }

    public function boot()
    {
        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations/tenant'),
        ], 'migrations');

        // Publish config
        $this->publishes([
            __DIR__ . '/../../config/nova-role-manager.php' => config_path('nova-role-manager.php'),
        ], 'config');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../../resources/lang' => resource_path('lang/vendor/nova-role-manager'),
        ], 'translations');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'nova-role-manager');

        // Register Nova resources
        Nova::resources([
            \NovaRoleManager\Nova\Role::class,
            \NovaRoleManager\Nova\Permission::class,
        ]);

        // Register observer to mark first user as superadmin
        $userModel = config('nova-role-manager.user_model', \App\Models\User::class);
        $userModel::observe(UserObserver::class);
    }
}
