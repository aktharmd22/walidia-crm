<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Policies are registered explicitly rather than auto-discovered, so a
     * model without a policy fails loudly instead of falling through to a
     * permissive default.
     *
     * @var array<class-string, class-string>
     */
    public array $policies = [
        //
    ];

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        /*
         * Admin holds everything. Granting it through a before-check rather
         * than a stored permission list means a permission added in a later
         * phase is never silently missing from the Admin role.
         *
         * It deliberately does NOT bypass gate rules: a hard gate still blocks
         * an Admin until they record an override with a reason (D-004).
         */
        Gate::before(function (User $user, string $ability): ?bool {
            return $user->hasRole(Roles::ADMIN) ? true : null;
        });
    }
}
