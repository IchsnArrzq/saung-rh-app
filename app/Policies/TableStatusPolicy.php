<?php

namespace App\Policies;

use App\Models\TableStatus;
use App\Models\User;

class TableStatusPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('table_status.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TableStatus $tableStatus): bool
    {
        return $user->hasPermissionTo('table_status.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('table_status.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TableStatus $tableStatus): bool
    {
        return $user->hasPermissionTo('table_status.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TableStatus $tableStatus): bool
    {
        return $user->hasPermissionTo('table_status.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TableStatus $tableStatus): bool
    {
        return $user->hasPermissionTo('table_status.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TableStatus $tableStatus): bool
    {
        return $user->hasPermissionTo('table_status.forceDelete');
    }
}
