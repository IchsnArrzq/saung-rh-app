<?php

namespace App\Policies;

use App\Models\ServiceLog;
use App\Models\User;

class ServiceLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('service_log.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceLog $serviceLog): bool
    {
        return $user->hasPermissionTo('service_log.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('service_log.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceLog $serviceLog): bool
    {
        return $user->hasPermissionTo('service_log.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceLog $serviceLog): bool
    {
        return $user->hasPermissionTo('service_log.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ServiceLog $serviceLog): bool
    {
        return $user->hasPermissionTo('service_log.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ServiceLog $serviceLog): bool
    {
        return $user->hasPermissionTo('service_log.forceDelete');
    }
}
