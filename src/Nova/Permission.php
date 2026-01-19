<?php

namespace NovaRoleManager\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class Permission extends Resource
{
    public static $model = \NovaRoleManager\Models\Permission::class;

    public static $title = 'name';

    public static $search = [
        'id',
        'name',
        'resource',
        'action',
    ];

    public static function label()
    {
        return __('nova-role-manager::permissions.label');
    }

    public static function singularLabel()
    {
        return __('nova-role-manager::permissions.singular');
    }

    public static function createButtonLabel()
    {
        return __('nova-role-manager::permissions.create');
    }

    public static function updateButtonLabel()
    {
        return __('nova-role-manager::permissions.update');
    }

    public function fields(NovaRequest $request)
    {
        $resources = config('nova-role-manager.resources', [
            'user' => 'User',
            'role' => 'Role',
            'permission' => 'Permission',
        ]);

        $actions = config('nova-role-manager.actions', [
            'view' => 'View',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
            'manage' => 'Manage',
        ]);

        return [
            ID::make()->sortable(),

            Text::make(__('nova-role-manager::permissions.name'), 'name')
                ->sortable()
                ->rules('required', 'string', 'unique:permissions,name,{{resourceId}}')
                ->creationRules('unique:permissions,name')
                ->readonly()
                ->hideWhenCreating(),

            Select::make(__('nova-role-manager::permissions.resource'), 'resource')
                ->options($resources)
                ->displayUsingLabels()
                ->sortable()
                ->rules('required', 'string')
                ->searchable(),

            Select::make(__('nova-role-manager::permissions.action'), 'action')
                ->options($actions)
                ->displayUsingLabels()
                ->sortable()
                ->rules('required', 'string')
                ->searchable(),

            Textarea::make(__('nova-role-manager::permissions.description'), 'description')
                ->nullable(),

            BelongsToMany::make(__('nova-role-manager::roles.label'), 'roles', Role::class)
                ->searchable()
                ->canSee(function ($request) {
                    return auth()->user()->hasPermission('manage.roles');
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
