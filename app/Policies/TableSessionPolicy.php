<?php

namespace App\Policies;

use App\Models\TableSession;
use App\Models\User;

class TableSessionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('table_session.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TableSession $tableSession): bool
    {
        return $user->checkPermissionTo('table_session.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('table_session.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TableSession $tableSession): bool
    {
        return $user->checkPermissionTo('table_session.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TableSession $tableSession): bool
    {
        return $user->checkPermissionTo('table_session.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TableSession $tableSession): bool
    {
        return $user->checkPermissionTo('table_session.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TableSession $tableSession): bool
    {
        return $user->checkPermissionTo('table_session.forceDelete');
    }
}
