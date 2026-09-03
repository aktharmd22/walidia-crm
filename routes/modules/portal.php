<?php

declare(strict_types=1);

use App\Http\Controllers\Portal\PortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portals
|--------------------------------------------------------------------------
|
| The only routes in the application that an unauthenticated stranger can
| reach with a URL alone — so they are deliberately few, deliberately
| read-only, and deliberately outside the `auth` group rather than exempted
| from it by a flag someone could flip.
|
| A link is single-purpose, expires in 7 days, is throttled per IP, and grants
| no session (see PortalGuard). Everything that can go wrong — expired,
| revoked, exhausted, wrong purpose, guessed — returns the same 404.
|
*/

Route::middleware(['portal', 'throttle:20,1'])->prefix('portal')->name('portal.')->group(function (): void {
    Route::get('/statement/{token}', [PortalController::class, 'ownerStatement'])->name('statement');
    Route::get('/assignment/{token}', [PortalController::class, 'crewAssignment'])->name('assignment');
    Route::get('/listing/{token}', [PortalController::class, 'listing'])->name('listing');
});
