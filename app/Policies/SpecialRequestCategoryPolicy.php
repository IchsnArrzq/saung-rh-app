<?php

namespace App\Policies;

use App\Models\SpecialRequestCategory;
use App\Models\User;

class SpecialRequestCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('special_request_category.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SpecialRequestCategory $specialRequestCategory): bool
    {
        return $user->checkPermissionTo('special_request_category.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('special_request_category.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SpecialRequestCategory $specialRequestCategory): bool
    {
        return $user->checkPermissionTo('special_request_category.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SpecialRequestCategory $specialRequestCategory): bool
    {
        return $user->checkPermissionTo('special_request_category.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SpecialRequestCategory $specialRequestCategory): bool
    {
        return $user->checkPermissionTo('special_request_category.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SpecialRequestCategory $specialRequestCategory): bool
    {
        return $user->checkPermissionTo('special_request_category.forceDelete');
    }
}
