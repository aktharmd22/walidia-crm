<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Me\ChromeController;
use App\Http\Controllers\Me\ProfileController;
use App\Http\Controllers\Me\SessionController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authenticated application
|--------------------------------------------------------------------------
|
| Everything behind `auth` also passes `two-factor`: enrolment is mandatory,
| so an account without a confirmed TOTP device reaches only the setup screen.
|
*/

Route::middleware(['auth', 'two-factor'])->group(function (): void {

    /* Dashboard ---------------------------------------------------------- */
    Route::get('/', [DashboardController::class, 'myDay'])->name('dashboard');
    Route::get('/dashboard/alerts', [DashboardController::class, 'alerts'])->name('dashboard.alerts');
    Route::get('/dashboard/calendar', [DashboardController::class, 'calendar'])->name('dashboard.calendar');

    /* Search ------------------------------------------------------------- */
    Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
    Route::get('/search', [SearchController::class, 'index'])->name('search.global');

    /* The signed-in user's own account ----------------------------------- */
    Route::prefix('me')->name('me.')->group(function (): void {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/security', [ProfileController::class, 'security'])->name('security');
        Route::get('/sessions', [SessionController::class, 'index'])->name('sessions');
        Route::delete('/sessions/{id}', [SessionController::class, 'destroy'])->name('sessions.destroy');
        Route::delete('/sessions', [SessionController::class, 'destroyOthers'])->name('sessions.destroy-others');

        // Chrome and accent are per-user preferences, stored server-side so the
        // first paint is already correct (D-009).
        // Preference toggles mutate state, so they are POST — never a GET a
        // browser or link prefetcher could fire on its own.
        Route::post('/chrome/{theme}', [ChromeController::class, 'theme'])->name('chrome');
        Route::post('/accent/{accent}', [ChromeController::class, 'accent'])->name('accent');
        Route::post('/locale/{locale}', [ChromeController::class, 'locale'])->name('locale');
    });

    /* Component gallery — local only ------------------------------------- */
    if (app()->environment('local')) {
        Route::get('/dev/ui', fn () => Inertia\Inertia::render('Dev/Ui'))->name('dev.ui');
    }
});

/* Two-factor enrolment: reachable while 2FA is still outstanding. */
Route::middleware('auth')->group(function (): void {
    Route::get('/two-factor/setup', [TwoFactorSetupController::class, 'show'])->name('two-factor.setup');
});

require __DIR__.'/modules/crm.php';
require __DIR__.'/modules/fleet.php';
require __DIR__.'/modules/charter.php';
require __DIR__.'/modules/brokerage.php';
require __DIR__.'/modules/operations.php';
require __DIR__.'/modules/management.php';
require __DIR__.'/modules/finance.php';
require __DIR__.'/modules/documents.php';
require __DIR__.'/modules/engine.php';
require __DIR__.'/modules/settings.php';
