<?php

declare(strict_types=1);

use App\Http\Controllers\Charter\BookingController;
use App\Http\Controllers\Charter\CharterEnquiryController;
use App\Http\Controllers\Charter\CharterProposalController;
use App\Http\Controllers\Charter\CostSheetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Charter
|--------------------------------------------------------------------------
|
| Enquiry → matching → proposal → booking → cost sheet.
|
| State changes are named POST routes rather than a PATCH of `status`, because
| each one is gate-evaluated before it happens and returns the reasons if it is
| refused (D-004).
|
*/

Route::middleware(['auth', 'two-factor'])->prefix('charter')->name('charter.')->group(function (): void {

    /* Enquiries ----------------------------------------------------------- */
    Route::controller(CharterEnquiryController::class)->prefix('enquiries')->name('enquiries.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{enquiry}/restore', 'restore')->withTrashed()->name('restore');
        Route::get('/{enquiry}/matching', 'matching')->name('matching');
        Route::post('/{enquiry}/shortlist', 'shortlist')->name('shortlist');
    });
    Route::resource('enquiries', CharterEnquiryController::class)->parameters(['enquiries' => 'enquiry']);

    /* Proposals ----------------------------------------------------------- */
    Route::controller(CharterProposalController::class)->prefix('proposals')->name('proposals.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{proposal}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{proposal}/send', 'send')->name('send');
        Route::post('/{proposal}/accept', 'accept')->name('accept');
        Route::post('/{proposal}/decline', 'decline')->name('decline');
        Route::post('/{proposal}/version', 'version')->name('version');
    });
    Route::resource('proposals', CharterProposalController::class)->parameters(['proposals' => 'proposal']);

    /* Bookings ------------------------------------------------------------ */
    Route::controller(BookingController::class)->prefix('bookings')->name('bookings.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{booking}/restore', 'restore')->withTrashed()->name('restore');

        // Guarded transitions.
        Route::post('/{booking}/contract', 'generateContract')->name('contract');
        Route::post('/{booking}/contract/signed', 'signContract')->name('contract.signed');
        Route::post('/{booking}/release-operations', 'releaseOperations')->name('release-operations');
        Route::post('/{booking}/confirm', 'confirm')->name('confirm');
        Route::post('/{booking}/cancel', 'cancel')->name('cancel');
        Route::post('/{booking}/complete', 'complete')->name('complete');
        Route::post('/{booking}/cost-sheet', [CostSheetController::class, 'forBooking'])->name('cost-sheet');
    });
    Route::resource('bookings', BookingController::class)->parameters(['bookings' => 'booking']);

    Route::get('/calendar', [BookingController::class, 'calendar'])->name('calendar');

    /* Cost sheets --------------------------------------------------------- */
    Route::controller(CostSheetController::class)->prefix('cost-sheets')->name('cost-sheets.')->group(function (): void {
        Route::get('/export', 'export')->name('export');
        Route::post('/{costSheet}/lines', 'storeLine')->name('lines.store');
        Route::put('/{costSheet}/lines/{line}', 'updateLine')->name('lines.update');
        Route::delete('/{costSheet}/lines/{line}', 'destroyLine')->name('lines.destroy');
        Route::post('/{costSheet}/copy-phase', 'copyPhase')->name('copy-phase');
        Route::post('/{costSheet}/close', 'close')->name('close');
    });
    Route::resource('cost-sheets', CostSheetController::class)
        ->parameters(['cost-sheets' => 'costSheet'])
        ->only(['index', 'show']);

    Route::get('/pnl', [CostSheetController::class, 'profitAndLoss'])->name('pnl');
});
