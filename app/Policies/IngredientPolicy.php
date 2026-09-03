<?php

namespace App\Policies;

use App\Models\Ingredient;
use App\Models\User;

class IngredientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('ingredient.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ingredient $ingredient): bool
    {
        return $user->checkPermissionTo('ingredient.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('ingredient.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ingredient $ingredient): bool
    {
        return $user->checkPermissionTo('ingredient.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ingredient $ingredient): bool
    {
        return $user->checkPermissionTo('ingredient.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ingredient $ingredient): bool
    {
        return $user->checkPermissionTo('ingredient.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ingredient $ingredient): bool
    {
        return $user->checkPermissionTo('ingredient.forceDelete');
    }
}
