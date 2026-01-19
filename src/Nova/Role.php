<?php

namespace NovaRoleManager\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
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
        return __('roles.label');
    }

    public static function singularLabel()
    {
        return __('roles.singular');
    }

    public static function createButtonLabel()
    {
        return __('roles.create');
    }

    public static function updateButtonLabel()
    {
        return __('roles.update');
    }

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make(__('roles.name'), 'name')
                ->sortable()
                ->rules('required', 'string', 'unique:roles,name,{{resourceId}}')
                ->creationRules('unique:roles,name'),

            Textarea::make(__('roles.description'), 'description')
                ->nullable(),

            BelongsToMany::make(__('permissions.label'), 'permissions', Permission::class)
                ->searchable()
                ->canSee(function ($request) {
                    return auth()->user()->hasPermissionTo('manage.permission');
                }),

            BelongsToMany::make(__('users.label'), 'users', \App\Nova\User::class)
                ->searchable()
                ->canSee(function ($request) {
                    return auth()->user()->hasPermissionTo('manage.user');
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
