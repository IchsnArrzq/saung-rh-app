<?php

namespace App\Policies;

use App\Models\PaymentAccount;
use App\Models\User;

class PaymentAccountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('payment_account.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PaymentAccount $paymentAccount): bool
    {
        return $user->hasPermissionTo('payment_account.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('payment_account.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PaymentAccount $paymentAccount): bool
    {
        return $user->hasPermissionTo('payment_account.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PaymentAccount $paymentAccount): bool
    {
        return $user->hasPermissionTo('payment_account.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PaymentAccount $paymentAccount): bool
    {
        return $user->hasPermissionTo('payment_account.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PaymentAccount $paymentAccount): bool
    {
        return $user->hasPermissionTo('payment_account.forceDelete');
    }
}
