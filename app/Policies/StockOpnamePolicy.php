<?php

namespace App\Policies;

use App\Models\StockOpname;
use App\Models\User;

class StockOpnamePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('stock_opname.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StockOpname $stockOpname): bool
    {
        return $user->checkPermissionTo('stock_opname.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('stock_opname.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StockOpname $stockOpname): bool
    {
        return $user->checkPermissionTo('stock_opname.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StockOpname $stockOpname): bool
    {
        return $user->checkPermissionTo('stock_opname.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StockOpname $stockOpname): bool
    {
        return $user->checkPermissionTo('stock_opname.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StockOpname $stockOpname): bool
    {
        return $user->checkPermissionTo('stock_opname.forceDelete');
    }
}
