<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Two-factor authentication is mandatory for every internal user, with no
 * exceptions (brief §4). Until it is confirmed, the only reachable routes are
 * the enrolment flow and sign-out.
 */
class EnsureTwoFactorIsConfirmed
{
    /** Routes reachable while enrolment is still outstanding. */
    private const ALLOWED = [
        'two-factor.setup',
        'logout',
        'user/two-factor-authentication',
        'user/confirmed-two-factor-authentication',
        'user/two-factor-qr-code',
        'user/two-factor-recovery-codes',
        'user/two-factor-secret-key',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! config('walidia.require_two_factor')) {
            return $next($request);
        }

        if ($user->hasTwoFactorConfirmed()) {
            return $next($request);
        }

        foreach (self::ALLOWED as $allowed) {
            if ($request->routeIs($allowed) || $request->is($allowed)) {
                return $next($request);
            }
        }

        return redirect()->route('two-factor.setup');
    }
}
