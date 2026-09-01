<?php

declare(strict_types=1);

use App\Http\Controllers\Fleet\MarinaController;
use App\Http\Controllers\Fleet\YachtController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fleet
|--------------------------------------------------------------------------
|
| One yacht registry with three capability flags (D-003), plus the marinas and
| the availability calendar that the whole charter side reads from.
|
*/

Route::middleware(['auth', 'two-factor'])->prefix('fleet')->name('fleet.')->group(function (): void {

    Route::controller(YachtController::class)->prefix('yachts')->name('yachts.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{yacht}/restore', 'restore')->withTrashed()->name('restore');
    });
    Route::resource('yachts', YachtController::class);

    Route::get('/charter-fleet', [YachtController::class, 'charterFleet'])->name('charter-fleet');
    Route::get('/for-sale', [YachtController::class, 'forSale'])->name('for-sale');
    Route::get('/availability', [YachtController::class, 'availability'])->name('availability');

    Route::controller(MarinaController::class)->prefix('marinas')->name('marinas.')->group(function (): void {
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{marina}/restore', 'restore')->withTrashed()->name('restore');
    });
    Route::resource('marinas', MarinaController::class);
});
