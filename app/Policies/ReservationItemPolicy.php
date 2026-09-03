<?php

namespace App\Policies;

use App\Models\ReservationItem;
use App\Models\User;

class ReservationItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('reservation_item.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ReservationItem $reservationItem): bool
    {
        return $user->checkPermissionTo('reservation_item.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('reservation_item.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ReservationItem $reservationItem): bool
    {
        return $user->checkPermissionTo('reservation_item.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ReservationItem $reservationItem): bool
    {
        return $user->checkPermissionTo('reservation_item.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ReservationItem $reservationItem): bool
    {
        return $user->checkPermissionTo('reservation_item.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ReservationItem $reservationItem): bool
    {
        return $user->checkPermissionTo('reservation_item.forceDelete');
    }
}
