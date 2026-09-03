<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Policies are auto-discovered: App\Models\Order pairs with
     * App\Policies\OrderPolicy by name, so nothing is registered by hand here.
     */
    public function boot(): void
    {
        $this->registerSuperadminBypass();
    }

    /**
     * Superadmin passes every gate without consulting a policy.
     *
     * Superadmin is also *granted* all 252 policy permissions by
     * PolicyPermissionSeeder, so this looks redundant — it is not. Adding a
     * model to PolicyPermissions::$models mints seven new permissions that
     * nobody holds until the seeder is re-run, and until then superadmin would
     * be locked out of the screen that was just built. The role means "no
     * restrictions", so it is expressed as a rule rather than as data that can
     * fall behind.
     *
     * Returning null (not false) on a non-superadmin is what lets the normal
     * policy chain continue; false would short-circuit into a denial.
     */
    private function registerSuperadminBypass(): void
    {
        Gate::before(fn (User $user) => $user->hasRole('superadmin') ? true : null);
    }
}
