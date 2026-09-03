<?php

declare(strict_types=1);

use App\Http\Controllers\Management\CertificateController;
use App\Http\Controllers\Management\MaintenanceJobController;
use App\Http\Controllers\Management\MaintenanceScheduleController;
use App\Http\Controllers\Management\ManagementAgreementController;
use App\Http\Controllers\Management\OwnerStatementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Management
|--------------------------------------------------------------------------
|
| Running someone else's yacht: the mandate, the certificates, the work, and
| the statement that tells the owner what they earned.
|
| The certificate register is load-bearing — a charter whose safety certificate
| has lapsed does not sail, and the dispatch gate reads this table to know.
|
*/

Route::middleware(['auth', 'two-factor'])->prefix('management')->name('management.')->group(function (): void {

    Route::controller(ManagementAgreementController::class)->prefix('agreements')->name('agreements.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{agreement}/restore', 'restore')->withTrashed()->name('restore');
    });
    Route::resource('agreements', ManagementAgreementController::class)->parameters(['agreements' => 'agreement']);

    /* Certificates — the compliance board and the dispatch gate's source ---- */
    Route::controller(CertificateController::class)->prefix('certificates')->name('certificates.')->group(function (): void {
        Route::get('/expiry', 'expiry')->name('expiry');
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{certificate}/restore', 'restore')->withTrashed()->name('restore');
    });
    Route::resource('certificates', CertificateController::class);

    Route::controller(MaintenanceJobController::class)->prefix('maintenance')->name('maintenance.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{maintenance}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{maintenance}/complete', 'complete')->name('complete');
    });
    Route::resource('maintenance', MaintenanceJobController::class)->parameters(['maintenance' => 'maintenance']);

    Route::controller(OwnerStatementController::class)->prefix('owner-statements')->name('owner-statements.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{ownerStatement}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{ownerStatement}/issue', 'issue')->name('issue');
        Route::post('/{ownerStatement}/share', 'share')->name('share');
    });
    Route::resource('owner-statements', OwnerStatementController::class)
        ->parameters(['owner-statements' => 'ownerStatement']);

    /* Preventive maintenance that recurs ---------------------------------- */
    Route::controller(MaintenanceScheduleController::class)->prefix('maintenance-schedules')->name('maintenance-schedules.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{maintenanceSchedule}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{maintenanceSchedule}/raise-job', 'raiseJob')->name('raise-job');
    });
    Route::resource('maintenance-schedules', MaintenanceScheduleController::class)
        ->parameters(['maintenance-schedules' => 'maintenanceSchedule']);
});
