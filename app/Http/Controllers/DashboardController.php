<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * My Day: blocked transitions first, then tasks, then what is on the water.
 *
 * The panels fill as later phases land — each reads from its own domain and
 * returns [] until that domain exists, so the screen is never broken, only
 * emptier than it will be.
 */
class DashboardController extends Controller
{
    public function myDay(Request $request): Response
    {
        return Inertia::render('Dashboard/MyDay', [
            'metrics' => [],
            'blockers' => [],
            'tasks' => [],
            'upcoming' => [],
        ]);
    }

    public function alerts(Request $request): Response
    {
        return Inertia::render('Dashboard/Alerts', [
            'hard' => [],
            'soft' => [],
            'expiring' => [],
        ]);
    }

    public function calendar(Request $request): Response
    {
        return Inertia::render('Dashboard/Calendar', [
            'events' => [],
            'month' => $request->query('month', now(config('walidia.display_timezone'))->format('Y-m')),
        ]);
    }
}
