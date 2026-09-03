<?php

namespace App\Policies;

use App\Models\MenuCategory;
use App\Models\User;

class MenuCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('menu_category.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MenuCategory $menuCategory): bool
    {
        return $user->checkPermissionTo('menu_category.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('menu_category.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MenuCategory $menuCategory): bool
    {
        return $user->checkPermissionTo('menu_category.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MenuCategory $menuCategory): bool
    {
        return $user->checkPermissionTo('menu_category.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MenuCategory $menuCategory): bool
    {
        return $user->checkPermissionTo('menu_category.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MenuCategory $menuCategory): bool
    {
        return $user->checkPermissionTo('menu_category.forceDelete');
    }
}
