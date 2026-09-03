<?php

namespace App\Policies;

use App\Models\Tip;
use App\Models\User;

class TipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('tip.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tip $tip): bool
    {
        return $user->checkPermissionTo('tip.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('tip.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tip $tip): bool
    {
        return $user->checkPermissionTo('tip.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tip $tip): bool
    {
        return $user->checkPermissionTo('tip.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tip $tip): bool
    {
        return $user->checkPermissionTo('tip.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tip $tip): bool
    {
        return $user->checkPermissionTo('tip.forceDelete');
    }
}
