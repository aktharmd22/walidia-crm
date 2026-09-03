<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\SignedLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

/**
 * Issuing a portal link.
 *
 * The plaintext token exists for exactly one round trip: it is returned to the
 * person who pressed the button, flashed to their screen so they can send it,
 * and never recoverable afterwards — the database holds only its hash. Losing
 * the link means issuing a new one, which is the correct behaviour.
 *
 * The caller writes the timeline entry, where the record's own type is known.
 */
trait IssuesPortalLinks
{
    protected function issuePortalLink(Model $subject, string $purpose, string $route, ?int $clientId = null): RedirectResponse
    {
        ['link' => $link, 'token' => $token] = SignedLink::issue($subject, $purpose, $clientId);

        return back()->with('portal_link', [
            'url' => route($route, $token),
            'expires_at' => $link->expires_at?->toIso8601String(),
        ]);
    }
}
