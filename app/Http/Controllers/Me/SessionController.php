<?php

declare(strict_types=1);

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Concurrent sessions, listed and revocable by the user (brief §4).
 * Requires the database session driver, which this application uses.
 */
class SessionController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Me/Sessions', [
            'sessions' => $this->sessions($request),
        ]);
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->delete();

        return back()->with('success', 'That session was signed out.');
    }

    public function destroyOthers(Request $request): RedirectResponse
    {
        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return back()->with('success', 'All other sessions were signed out.');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sessions(Request $request): array
    {
        return DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn (object $session): array => [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_active' => date(DATE_ATOM, (int) $session->last_activity),
                'is_current' => $session->id === $request->session()->getId(),
            ])
            ->all();
    }
}
