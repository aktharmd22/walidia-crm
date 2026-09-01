<?php

declare(strict_types=1);

use App\Http\Controllers\GateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| The gate engine
|--------------------------------------------------------------------------
|
| `evaluate` is the dry run every screen calls so a disabled button can say
| exactly what is missing. The Override Register is read-only for everyone,
| including Admin — there is no route here that writes to it.
|
*/

Route::middleware(['auth', 'two-factor'])->group(function (): void {
    Route::post('/gates/evaluate', [GateController::class, 'evaluate'])->name('gates.evaluate');

    Route::get('/compliance/overrides', [GateController::class, 'overrides'])->name('compliance.overrides');

    Route::prefix('automation')->name('automation.')->group(function (): void {
        Route::get('/gate-rules', [GateController::class, 'rules'])->name('gate-rules.index');
        Route::post('/gate-rules/{rule}/toggle', [GateController::class, 'toggleRule'])->name('gate-rules.toggle');
    });
});
