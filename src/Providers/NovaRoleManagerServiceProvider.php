<?php

namespace NowakAdmin\NovaRoleManager\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use NowakAdmin\NovaRoleManager\Nova\Permission;
use Spatie\Permission\PermissionRegistrar;

class NovaRoleManagerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/nova-role-manager.php',
            'nova-role-manager'
        );

        // Translations must be registered before any provider's boot() calls __(),
        // otherwise the JSON translation cache is frozen without this package's keys.
        $this->loadJsonTranslationsFrom(__DIR__.'/../../resources/lang');
    }

    public function boot()
    {
        // Publish migrations
        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations/tenant'),
        ], 'nova-role-manager-migrations');

        // Publish config
        $this->publishes([
            __DIR__.'/../../config/nova-role-manager.php' => config_path('nova-role-manager.php'),
        ], 'nova-role-manager-config');

        // Publish translations
        $this->publishes([
            __DIR__.'/../../resources/lang' => resource_path('lang/vendor/nova-role-manager'),
        ], 'nova-role-manager-translations');

        // Register translations with Nova
        Nova::serving(function () {
            $locale = app()->getLocale();
            $langPath = __DIR__.'/../../resources/lang/'.$locale.'.json';

            if (file_exists($langPath)) {
                Nova::translations($langPath);
            }
        });

        // Register Nova resources
        Nova::resources([
            \NowakAdmin\NovaRoleManager\Nova\Role::class,
            Permission::class,
        ]);

        // Configure Spatie permission models
        // This uses the tenant-aware models from this package
        $this->configureSpatie();
    }

    private function configureSpatie()
    {
        // Use our tenant-aware models
        app()->make(PermissionRegistrar::class)
            ->setPermissionClass(\NowakAdmin\NovaRoleManager\Models\Permission::class)
            ->setRoleClass(\NowakAdmin\NovaRoleManager\Models\Role::class);
    }
}
