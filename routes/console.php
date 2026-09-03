<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled work
|--------------------------------------------------------------------------
|
| Everything the system does without being asked. All of it is idempotent —
| a run is unique per rule and record — so a missed night catches up rather
| than double-sending.
|
*/

// Hourly, because a charter reminder that is three hours late is worse than
// one that costs a few queries.
Schedule::command('walidia:automation')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
