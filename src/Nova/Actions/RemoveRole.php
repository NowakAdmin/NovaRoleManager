<?php

namespace NovaRoleManager\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\MultiSelect;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaRoleManager\Models\Role;

class RemoveRole extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Get the displayable name of the action.
     */
    public function name(): string
    {
        return __('Remove Roles');
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) {
            // Get role objects - let Spatie handle guard automatically
            $roleNames = $fields->roles;
            $roles = Role::whereIn('name', $roleNames)->get();
            
            foreach ($roles as $role) {
                $model->removeRole($role);
            }
        }

        return Action::message(__('Roles removed successfully!'));
    }

    /**
     * Get the fields available on the action.
     */
    public function fields(NovaRequest $request): array
    {
        return [
            MultiSelect::make(__('Roles'), 'roles')
                ->options(Role::pluck('name', 'name')->toArray())
                ->rules('required')
                ->help(__('Select one or more roles to remove from the selected users.')),
        ];
    }
}
