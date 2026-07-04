<?php

namespace App\Policies;

use App\Models\MenuIngredient;
use App\Models\User;

class MenuIngredientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('menu_ingredient.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MenuIngredient $menuIngredient): bool
    {
        return $user->hasPermissionTo('menu_ingredient.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('menu_ingredient.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MenuIngredient $menuIngredient): bool
    {
        return $user->hasPermissionTo('menu_ingredient.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MenuIngredient $menuIngredient): bool
    {
        return $user->hasPermissionTo('menu_ingredient.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MenuIngredient $menuIngredient): bool
    {
        return $user->hasPermissionTo('menu_ingredient.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MenuIngredient $menuIngredient): bool
    {
        return $user->hasPermissionTo('menu_ingredient.forceDelete');
    }
}
