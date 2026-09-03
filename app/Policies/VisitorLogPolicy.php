<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitorLog;

class VisitorLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('visitor_log.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VisitorLog $visitorLog): bool
    {
        return $user->checkPermissionTo('visitor_log.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('visitor_log.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VisitorLog $visitorLog): bool
    {
        return $user->checkPermissionTo('visitor_log.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VisitorLog $visitorLog): bool
    {
        return $user->checkPermissionTo('visitor_log.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VisitorLog $visitorLog): bool
    {
        return $user->checkPermissionTo('visitor_log.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VisitorLog $visitorLog): bool
    {
        return $user->checkPermissionTo('visitor_log.forceDelete');
    }
}
