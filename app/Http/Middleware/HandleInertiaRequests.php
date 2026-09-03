<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Navigation;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Shared with every page. Permissions are shared so the React layer knows
     * what to render — it never decides what is allowed; the server does that
     * in a policy on every request.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->avatarUrl(),
                    'locale' => $user->locale,
                    'roles' => $user->getRoleNames()->all(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->all(),
                    'two_factor_enabled' => $user->hasTwoFactorConfirmed(),
                ] : null,
            ],

            'nav' => fn (): array => Navigation::for($user),

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],

            'chrome' => [
                'theme' => $user instanceof User ? $user->chrome : 'light',
                'accent' => $user instanceof User ? $user->accent : 'brass',
            ],

            'locale' => app()->getLocale(),
            'direction' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',

            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'currency' => config('walidia.currency'),
                'timezone' => config('walidia.display_timezone'),
            ],
        ]);
    }
}
