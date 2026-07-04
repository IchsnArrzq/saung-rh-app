<?php

namespace App\Policies;

use App\Models\AppSetting;
use App\Models\User;

class AppSettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('app_setting.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AppSetting $appSetting): bool
    {
        return $user->hasPermissionTo('app_setting.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('app_setting.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AppSetting $appSetting): bool
    {
        return $user->hasPermissionTo('app_setting.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AppSetting $appSetting): bool
    {
        return $user->hasPermissionTo('app_setting.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AppSetting $appSetting): bool
    {
        return $user->hasPermissionTo('app_setting.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AppSetting $appSetting): bool
    {
        return $user->hasPermissionTo('app_setting.forceDelete');
    }
}
