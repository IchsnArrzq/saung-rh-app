<?php

namespace App\Policies;

use App\Models\MenuStatus;
use App\Models\User;

class MenuStatusPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('menu_status.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MenuStatus $menuStatus): bool
    {
        return $user->hasPermissionTo('menu_status.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('menu_status.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MenuStatus $menuStatus): bool
    {
        return $user->hasPermissionTo('menu_status.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MenuStatus $menuStatus): bool
    {
        return $user->hasPermissionTo('menu_status.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MenuStatus $menuStatus): bool
    {
        return $user->hasPermissionTo('menu_status.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MenuStatus $menuStatus): bool
    {
        return $user->hasPermissionTo('menu_status.forceDelete');
    }
}
