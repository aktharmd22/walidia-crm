<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The user's locale drives both translation and `dir` on <html>; RTL is a
 * first-class layout, not a retrofit (D-012).
 */
class SetLocaleAndTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $locale = $user instanceof User
            ? $user->locale
            : ($request->session()->get('locale') ?? config('app.locale'));

        if (in_array($locale, config('walidia.locales', ['en']), true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
