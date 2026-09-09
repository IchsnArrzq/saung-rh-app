<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('purchase.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Purchase $purchase): bool
    {
        return $user->checkPermissionTo('purchase.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('purchase.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Purchase $purchase): bool
    {
        return $user->checkPermissionTo('purchase.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Purchase $purchase): bool
    {
        return $user->checkPermissionTo('purchase.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Purchase $purchase): bool
    {
        return $user->checkPermissionTo('purchase.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Purchase $purchase): bool
    {
        return $user->checkPermissionTo('purchase.forceDelete');
    }
}
