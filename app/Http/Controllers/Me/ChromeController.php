<?php

declare(strict_types=1);

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Chrome, accent and locale are stored on the user so the server can stamp
 * them onto <html> before first paint — no flash of the wrong theme, and no
 * client-side theme state to get out of step with the session.
 */
class ChromeController extends Controller
{
    public function theme(Request $request, string $theme): RedirectResponse
    {
        $this->guard($theme, ['navy', 'light']);

        $request->user()->forceFill(['chrome' => $theme])->save();

        return back();
    }

    public function accent(Request $request, string $accent): RedirectResponse
    {
        $this->guard($accent, ['brass', 'blue']);

        $request->user()->forceFill(['accent' => $accent])->save();

        return back();
    }

    public function locale(Request $request, string $locale): RedirectResponse
    {
        $this->guard($locale, config('walidia.locales', ['en']));

        $request->user()->forceFill(['locale' => $locale])->save();

        return back();
    }

    /**
     * @param  list<string>  $allowed
     */
    private function guard(string $value, array $allowed): void
    {
        if (! in_array($value, $allowed, true)) {
            throw ValidationException::withMessages([
                'preference' => 'That is not one of the available options.',
            ]);
        }
    }
}
