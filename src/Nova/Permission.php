<?php

namespace NowakAdmin\NovaRoleManager\Nova;

use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Illuminate\Support\Str;

class Permission extends Resource
{
    public static $model = \NowakAdmin\NovaRoleManager\Models\Permission::class;

    public static $title = 'name';

    public static $search = [
        'id',
        'name',
        'resource',
        'action',
    ];

    public static function label()
    {
        return __('permissions.label');
    }

    public static function singularLabel()
    {
        return __('permissions.singular');
    }

    public static function createButtonLabel()
    {
        return __('permissions.create');
    }

    public static function updateButtonLabel()
    {
        return __('permissions.update');
    }

    public function fields(NovaRequest $request)
    {
        $resources = $this->resourceOptions();

        // Match BasePolicy methods
        $actions = config('nova-role-manager.actions', [
            'view' => 'View',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
            'restore' => 'Restore',
            'force_delete' => 'Force Delete',
        ]);

        return [
            ID::make()->sortable(),

            Text::make(__('permissions.name'), 'name')
                ->sortable()
                ->rules('required', 'string', 'unique:permissions,name,{{resourceId}}')
                ->creationRules('unique:permissions,name')
                ->readonly()
                ->hideWhenCreating(),

            Select::make(__('permissions.resource'), 'resource')
                ->options($resources)
                ->displayUsingLabels()
                ->sortable()
                ->rules('required', 'string')
                ->searchable(),

            Select::make(__('permissions.action'), 'action')
                ->options($actions)
                ->displayUsingLabels()
                ->sortable()
                ->rules('required', 'string')
                ->searchable(),

            Textarea::make(__('permissions.description'), 'description')
                ->nullable(),

            BelongsToMany::make(__('roles.label'), 'roles', Role::class)
                ->searchable()
                ->canSee(function ($request) {
                    try {
                        return auth()->check() && auth()->user()->hasPermission('update.role');
                    } catch (\Exception $e) {
                        return true;
                    }
                }),
        ];
    }

    /**
     * Build the resource dropdown options from the unified registry, keyed
     * by each entry's permission key (falls back to Str::snake(classname)),
     * using registry.labels overrides or Str::headline($key) as the label.
     *
     * @return array<string, string>
     */
    protected function resourceOptions(): array
    {
        $labels = config('registry.labels', []);
        $options = [];

        foreach (config('registry.entries', []) as $class => $entry) {
            $key = $entry['key'] ?? Str::snake(class_basename($class));
            $options[$key] = $labels[$key] ?? Str::headline($key);
        }

        return $options;
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
