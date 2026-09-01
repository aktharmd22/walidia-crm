<?php

declare(strict_types=1);

use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Finance
|--------------------------------------------------------------------------
|
| Invoices are issued, voided and credited — never edited once issued, and
| never deleted (D-013). Clearing a payment is what unlocks Operational
| Release, so it is its own route with its own permission.
|
*/

Route::middleware(['auth', 'two-factor'])->prefix('finance')->name('finance.')->group(function (): void {

    Route::controller(InvoiceController::class)->prefix('invoices')->name('invoices.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{invoice}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{invoice}/issue', 'issue')->name('issue');
        Route::post('/{invoice}/void', 'void')->name('void');
        Route::post('/{invoice}/credit-note', 'creditNote')->name('credit-note');
    });
    Route::resource('invoices', InvoiceController::class);
    Route::get('/overdue', [InvoiceController::class, 'overdue'])->name('overdue');

    Route::controller(PaymentController::class)->prefix('payments')->name('payments.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{payment}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{payment}/confirm-deposit', 'confirmDeposit')->name('confirm-deposit');
        Route::post('/{payment}/reconcile', 'reconcile')->name('reconcile');
        Route::post('/{payment}/allocate', 'allocateTo')->name('allocate');
    });
    Route::resource('payments', PaymentController::class);
});
