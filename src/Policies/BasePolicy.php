<?php

namespace NowakAdmin\NovaRoleManager\Policies;

use Illuminate\Database\Eloquent\Model;
use NowakAdmin\BizantiCore\Models\User;
use NowakAdmin\BizantiLicensing\Services\LicenseService;

abstract class BasePolicy
{
    /**
     * Module license key required for this policy.
     */
    protected string $licensedModule = '';

    /**
     * Determine if any action is allowed (called first).
     */
    public function before($user, $ability)
    {
        if (! $user instanceof User) {
            return null;
        }

        return null;
    }

    /**
     * Get the license service.
     */
    protected function licenseService(): LicenseService
    {
        return app(LicenseService::class);
    }

    /**
     * Check whether this policy has a module license configured.
     */
    protected function requiresLicense(): bool
    {
        return $this->licensedModule !== '';
    }

    /**
     * Check if user's tenant is licensed for this policy.
     */
    protected function isLicensed(User $user): bool
    {
        if (! $this->requiresLicense()) {
            return true;
        }

        if (! $user->tenant_id) {
            return false;
        }

        return $this->licenseService()->hasTenantLicense($user->tenant_id, $this->licensedModule);
    }

    /**
     * Determine if the user can view the model.
     */
    public function view($user, Model $model)
    {
        return $user instanceof User
            && $this->isLicensed($user)
            && $user->hasPermission('view.'.$this->getResourceName());
    }

    /**
     * Determine if the user can create models.
     */
    public function create($user)
    {
        return $user instanceof User
            && $this->isLicensed($user)
            && $user->hasPermission('create.'.$this->getResourceName());
    }

    /**
     * Determine if the user can update the model.
     */
    public function update($user, Model $model)
    {
        return $user instanceof User
            && $this->isLicensed($user)
            && $user->hasPermission('update.'.$this->getResourceName());
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete($user, Model $model)
    {
        return $user instanceof User
            && $this->isLicensed($user)
            && $user->hasPermission('delete.'.$this->getResourceName());
    }

    /**
     * Determine if the user can restore the model.
     */
    public function restore($user, Model $model)
    {
        return $user instanceof User
            && $this->isLicensed($user)
            && $user->hasPermission('restore.'.$this->getResourceName());
    }

    /**
     * Determine if the user can permanently delete the model.
     */
    public function forceDelete($user, Model $model)
    {
        return $user instanceof User
            && $this->isLicensed($user)
            && $user->hasPermission('force_delete.'.$this->getResourceName());
    }

    /**
     * Determine if the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->isLicensed($user) && $user->hasPermission('view.'.$this->getResourceName());
    }

    /**
     * Get the resource name for permission checking.
     * Override in child classes.
     */
    abstract protected function getResourceName(): string;
}
