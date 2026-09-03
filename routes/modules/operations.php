<?php

declare(strict_types=1);

use App\Http\Controllers\Charter\CharterDayController;
use App\Http\Controllers\Operations\CrewController;
use App\Http\Controllers\Operations\DamageReportController;
use App\Http\Controllers\Operations\IncidentController;
use App\Http\Controllers\Operations\SecurityDepositController;
use App\Http\Controllers\Operations\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Operations
|--------------------------------------------------------------------------
|
| Crew, vendors, and the charter day itself. Dispatch and boarding are guarded;
| closing a damage inspection is what releases the security deposit.
|
*/

Route::middleware(['auth', 'two-factor'])->group(function (): void {

    /* Charter Day — mobile-first, one action per tap ---------------------- */
    Route::controller(CharterDayController::class)->prefix('charter/day')->name('charter.day.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/{booking}', 'show')->name('show');
        Route::post('/{booking}/board', 'board')->name('board');
        Route::post('/{booking}/guests/{guest}/verify', 'verifyGuest')->name('guests.verify');
        Route::post('/{booking}/log', 'log')->name('log');
        Route::post('/{booking}/extras', 'storeExtra')->name('extras.store');
        Route::post('/{booking}/checklist/{item}/complete', 'completeChecklistItem')->name('checklist.complete');
        Route::post('/{booking}/incidents', 'reportIncident')->name('incidents.store');
        Route::post('/{booking}/damage', 'reportDamage')->name('damage.store');
    });

    /* Crew ---------------------------------------------------------------- */
    Route::controller(CrewController::class)->prefix('crew')->name('crew.')->group(function (): void {
        Route::get('/expiry', 'expiry')->name('expiry');
        Route::get('/assignments', 'assignments')->name('assignments.index');
        Route::get('/payouts', 'payouts')->name('payouts.index');
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{crew}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/assignments', 'storeAssignment')->name('assignments.store');
        Route::post('/assignments/{assignment}/dispatch', 'dispatch')->name('assignments.dispatch');
        Route::post('/assignments/{assignment}/share', 'shareAssignment')->name('assignments.share');
        Route::post('/{crew}/documents', 'storeDocument')->name('documents.store');
    });
    Route::resource('crew', CrewController::class)->parameters(['crew' => 'crew']);

    /* Vendors -------------------------------------------------------------- */
    Route::controller(VendorController::class)->prefix('vendors')->name('vendors.')->group(function (): void {
        Route::get('/categories', 'categories')->name('categories.index');
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{vendor}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{vendor}/approve', 'approve')->name('approve');
        Route::post('/{vendor}/ratings', 'rate')->name('ratings.store');
    });
    Route::resource('vendors', VendorController::class);

    /* Incidents, damage and deposits --------------------------------------- */
    Route::controller(IncidentController::class)->prefix('charter/incidents')->name('charter.incidents.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{incident}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{incident}/close', 'close')->name('close');
    });
    Route::resource('charter/incidents', IncidentController::class)->parameters(['incidents' => 'incident'])->names('charter.incidents');

    Route::controller(DamageReportController::class)->prefix('charter/damage-reports')->name('charter.damage-reports.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{damageReport}/restore', 'restore')->withTrashed()->name('restore');
        // Closing this is what unlocks the deposit release.
        Route::post('/{damageReport}/close', 'close')->name('close');
    });
    Route::resource('charter/damage-reports', DamageReportController::class)
        ->parameters(['damage-reports' => 'damageReport'])
        ->names('charter.damage-reports');

    Route::controller(SecurityDepositController::class)->prefix('finance/security-deposits')->name('finance.security-deposits.')->group(function (): void {
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{securityDeposit}/collect', 'collect')->name('collect');
        Route::post('/{securityDeposit}/release', 'release')->name('release');
    });
    Route::resource('finance/security-deposits', SecurityDepositController::class)
        ->parameters(['security-deposits' => 'securityDeposit'])
        ->names('finance.security-deposits');
});
