<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The portal grants no session.
 *
 * A signed link is a key to one document, not a way in. This middleware makes
 * that structural rather than a matter of remembering: any authenticated user
 * is forgotten for the duration of the request, and the response is told never
 * to be cached by a proxy or a shared browser — these links land in WhatsApp
 * threads and hotel business centres.
 */
class PortalGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        // A staff member opening a client's link sees exactly what the client
        // sees, never their own elevated view.
        auth()->forgetGuards();

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        // A signed token in a URL must not leak to whatever the reader clicks next.
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

        return $response;
    }
}
