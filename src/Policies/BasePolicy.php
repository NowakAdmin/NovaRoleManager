<?php

namespace NowakAdmin\NovaRoleManager\Policies;

use Illuminate\Support\Str;
use NowakAdmin\BizantiCore\Models\User;

class BasePolicy
{
    /**
     * The model class detected from the Gate arguments.
     * Set in before() and used by ability methods for resource name detection.
     */
    private string $detectedModelClass = '';

    /**
     * Run before every policy method.
     *
     * Denies non-User principals immediately. Detects the model/resource class
     * from the Gate argument so ability methods can derive the permission name.
     * License enforcement is handled globally via Gate::before() in AuthServiceProvider.
     */
    public function before(mixed $user, string $ability, mixed $argument = null): ?bool
    {
        if (! $user instanceof User) {
            return false;
        }

        if (is_object($argument)) {
            $this->detectedModelClass = get_class($argument);
        } elseif (is_string($argument) && class_exists($argument)) {
            $this->detectedModelClass = $argument;
        }

        return null;
    }

    /**
     * Determine if the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view.'.$this->getResourceName());
    }

    /**
     * Determine if the user can view the model.
     */
    public function view(User $user, mixed $model): bool
    {
        return $user->hasPermission('view.'.$this->getResourceName());
    }

    /**
     * Determine if the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create.'.$this->getResourceName());
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, mixed $model): bool
    {
        return $user->hasPermission('update.'.$this->getResourceName());
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, mixed $model): bool
    {
        return $user->hasPermission('delete.'.$this->getResourceName());
    }

    /**
     * Determine if the user can restore the model.
     */
    public function restore(User $user, mixed $model): bool
    {
        return $user->hasPermission('restore.'.$this->getResourceName());
    }

    /**
     * Determine if the user can permanently delete the model.
     */
    public function forceDelete(User $user, mixed $model): bool
    {
        return $user->hasPermission('force_delete.'.$this->getResourceName());
    }

    /**
     * Derive the resource name for permission building.
     * Uses the detected model/resource class basename in snake_case.
     */
    protected function getResourceName(): string
    {
        if ($this->detectedModelClass === '') {
            return '';
        }

        return Str::snake(class_basename($this->detectedModelClass));
    }
}
