<?php

declare(strict_types=1);

use App\Http\Controllers\Brokerage\BuyerRequirementController;
use App\Http\Controllers\Brokerage\HandoverController;
use App\Http\Controllers\Brokerage\ListingController;
use App\Http\Controllers\Brokerage\NdaController;
use App\Http\Controllers\Brokerage\OfferController;
use App\Http\Controllers\Brokerage\SurveyController;
use App\Http\Controllers\Brokerage\TransactionController;
use App\Http\Controllers\Brokerage\ViewingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Brokerage
|--------------------------------------------------------------------------
|
| Listing → NDA → viewing → offer → survey → transaction → handover.
|
| Three gates stand on that path: no viewing without a signed NDA and a
| verified buyer, no offer without proof of funds, and no ownership transfer
| until the money has cleared and AML is clear. Each is a guarded route, and
| each names what is missing rather than failing silently.
|
*/

Route::middleware(['auth', 'two-factor'])->prefix('brokerage')->name('brokerage.')->group(function (): void {

    /* Listings ------------------------------------------------------------ */
    Route::controller(ListingController::class)->prefix('listings')->name('listings.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{listing}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{listing}/publish', 'publish')->name('publish');
        Route::post('/{listing}/withdraw', 'withdraw')->name('withdraw');
    });
    Route::resource('listings', ListingController::class);

    /* Buyer requirements -------------------------------------------------- */
    Route::controller(BuyerRequirementController::class)->prefix('buyer-requirements')->name('buyer-requirements.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{buyerRequirement}/restore', 'restore')->withTrashed()->name('restore');
    });
    Route::resource('buyer-requirements', BuyerRequirementController::class)
        ->parameters(['buyer-requirements' => 'buyerRequirement']);

    /* NDAs — the gate in front of every viewing --------------------------- */
    Route::controller(NdaController::class)->prefix('ndas')->name('ndas.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{nda}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{nda}/signed', 'markSigned')->name('signed');
    });
    Route::resource('ndas', NdaController::class);

    /* Viewings ------------------------------------------------------------ */
    Route::controller(ViewingController::class)->prefix('viewings')->name('viewings.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{viewing}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{viewing}/schedule', 'schedule')->name('schedule');
        Route::post('/{viewing}/complete', 'complete')->name('complete');
    });
    Route::resource('viewings', ViewingController::class);

    /* Offers -------------------------------------------------------------- */
    Route::controller(OfferController::class)->prefix('offers')->name('offers.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{offer}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{offer}/submit', 'submit')->name('submit');
        Route::post('/{offer}/respond', 'respond')->name('respond');
    });
    Route::resource('offers', OfferController::class);

    /* Surveys and sea trials ---------------------------------------------- */
    Route::controller(SurveyController::class)->prefix('surveys')->name('surveys.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{survey}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{survey}/record', 'record')->name('record');
    });
    Route::resource('surveys', SurveyController::class);

    /* Transactions — the ownership transfer gate lives here ---------------- */
    Route::controller(TransactionController::class)->prefix('transactions')->name('transactions.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{transaction}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{transaction}/funds', 'recordFunds')->name('funds');
        Route::post('/{transaction}/aml', 'clearAml')->name('aml');
        Route::post('/{transaction}/transfer-ownership', 'transferOwnership')->name('transfer-ownership');
    });
    Route::resource('transactions', TransactionController::class);

    /* Handover ------------------------------------------------------------ */
    Route::controller(HandoverController::class)->prefix('handovers')->name('handovers.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{handover}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{handover}/complete', 'complete')->name('complete');
    });
    Route::resource('handovers', HandoverController::class);
});
