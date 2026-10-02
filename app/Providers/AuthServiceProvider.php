<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Super Admin bypasses every Permission/Role Check app-wide — returning
        // null (not false) falls through to the normal Gate/Policy Check for
        // every other User, per Laravel's Gate::before() contract.
        Gate::before(function (User $user, string $ability) {
            return $user->hasAllAccess() ? true : null;
        });
    }
}
