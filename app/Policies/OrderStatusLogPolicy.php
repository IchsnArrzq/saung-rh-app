<?php

namespace App\Policies;

use App\Models\OrderStatusLog;
use App\Models\User;

class OrderStatusLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('order_status_log.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrderStatusLog $orderStatusLog): bool
    {
        return $user->checkPermissionTo('order_status_log.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('order_status_log.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrderStatusLog $orderStatusLog): bool
    {
        return $user->checkPermissionTo('order_status_log.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrderStatusLog $orderStatusLog): bool
    {
        return $user->checkPermissionTo('order_status_log.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrderStatusLog $orderStatusLog): bool
    {
        return $user->checkPermissionTo('order_status_log.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrderStatusLog $orderStatusLog): bool
    {
        return $user->checkPermissionTo('order_status_log.forceDelete');
    }
}
