<?php

namespace NovaRoleManager\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use Spatie\Permission\Models\Role;

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

        // Load JSON translations for PHP __() calls
        $this->loadJsonTranslationsFrom(__DIR__ . '/../../resources/lang');

        // Register translations with Nova
        Nova::serving(function () {
            $locale = app()->getLocale();
            $langPath = __DIR__ . '/../../resources/lang/' . $locale . '.json';
            
            if (file_exists($langPath)) {
                Nova::translations($langPath);
            }
        });

        // Register Nova resources
        Nova::resources([
            \NovaRoleManager\Nova\Role::class,
            \NovaRoleManager\Nova\Permission::class,
        ]);

        // Configure Spatie permission models
        // This uses the tenant-aware models from this package
        $this->configureSpatie();
    }

    private function configureSpatie()
    {
        // Use our tenant-aware models
        app()->make(\Spatie\Permission\PermissionRegistrar::class)
            ->setPermissionClass(\NovaRoleManager\Models\Permission::class)
            ->setRoleClass(\NovaRoleManager\Models\Role::class);
    }
}
