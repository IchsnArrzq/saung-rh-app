<?php

namespace App\Policies;

use App\Models\OrderNote;
use App\Models\User;

class OrderNotePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('order_note.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrderNote $orderNote): bool
    {
        return $user->checkPermissionTo('order_note.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('order_note.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrderNote $orderNote): bool
    {
        return $user->checkPermissionTo('order_note.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrderNote $orderNote): bool
    {
        return $user->checkPermissionTo('order_note.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrderNote $orderNote): bool
    {
        return $user->checkPermissionTo('order_note.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrderNote $orderNote): bool
    {
        return $user->checkPermissionTo('order_note.forceDelete');
    }
}
