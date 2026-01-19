<?php

namespace NovaRoleManager\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class Role extends Resource
{
    public static $model = \NovaRoleManager\Models\Role::class;

    public static $title = 'name';

    public static $search = [
        'id',
        'name',
    ];

    public static function label()
    {
        return __('nova-role-manager::roles.label');
    }

    public static function singularLabel()
    {
        return __('nova-role-manager::roles.singular');
    }

    public static function createButtonLabel()
    {
        return __('nova-role-manager::roles.create');
    }

    public static function updateButtonLabel()
    {
        return __('nova-role-manager::roles.update');
    }

    public function fields(NovaRequest $request)
    {
        $userModel = config('nova-role-manager.user_model', \App\Models\User::class);

        return [
            ID::make()->sortable(),

            Text::make(__('nova-role-manager::roles.name'), 'name')
                ->sortable()
                ->rules('required', 'string', 'unique:nrm_roles,name,{{resourceId}}')
                ->creationRules('unique:nrm_roles,name'),

            Textarea::make(__('nova-role-manager::roles.description'), 'description')
                ->nullable(),

            Boolean::make(__('nova-role-manager::roles.is_superadmin'), 'is_superadmin')
                ->canSee(function ($request) {
                    return auth()->user()->isSuperAdmin();
                }),

            BelongsToMany::make(__('nova-role-manager::permissions.label'), 'permissions', Permission::class)
                ->searchable()
                ->canSee(function ($request) {
                    return auth()->user()->hasPermission('manage.permissions');
                }),

            BelongsToMany::make(__('nova-role-manager::users.label'), 'users')
                ->searchable()
                ->canSee(function ($request) {
                    return auth()->user()->hasPermission('manage.users');
                }),
        ];
    }

    public function cards(NovaRequest $request)
    {
        return [];
    }

    public function filters(NovaRequest $request)
    {
        return [];
    }

    public function lenses(NovaRequest $request)
    {
        return [];
    }

    public function actions(NovaRequest $request)
    {
        return [];
    }
}
