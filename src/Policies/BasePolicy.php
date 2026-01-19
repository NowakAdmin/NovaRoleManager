<?php

namespace NovaRoleManager\Policies;

use Illuminate\Database\Eloquent\Model;

abstract class BasePolicy
{
    /**
     * Determine if any action is allowed (called first)
     */
    public function before($user, $ability)
    {
        if ($user && $user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine if the user can view the model
     */
    public function view($user, Model $model)
    {
        return $user && $user->hasPermission('view.' . $this->getResourceName());
    }

    /**
     * Determine if the user can create models
     */
    public function create($user)
    {
        return $user && $user->hasPermission('create.' . $this->getResourceName());
    }

    /**
     * Determine if the user can update the model
     */
    public function update($user, Model $model)
    {
        return $user && $user->hasPermission('update.' . $this->getResourceName());
    }

    /**
     * Determine if the user can delete the model
     */
    public function delete($user, Model $model)
    {
        return $user && $user->hasPermission('delete.' . $this->getResourceName());
    }

    /**
     * Determine if the user can restore the model
     */
    public function restore($user, Model $model)
    {
        return $user && $user->hasPermission('restore.' . $this->getResourceName());
    }

    /**
     * Determine if the user can permanently delete the model
     */
    public function forceDelete($user, Model $model)
    {
        return $user && $user->hasPermission('force_delete.' . $this->getResourceName());
    }

    /**
     * Get the resource name for permission checking
     * Override in child classes
     */
    abstract protected function getResourceName(): string;
}
