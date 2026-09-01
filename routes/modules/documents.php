<?php

declare(strict_types=1);

use App\Http\Controllers\Documents\DocumentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Documents
|--------------------------------------------------------------------------
|
| The vault. No file is addressable without passing through the download
| route, which authorises, logs the access and then signs a short-lived URL
| (D-015).
|
*/

Route::middleware(['auth', 'two-factor'])->group(function (): void {

    Route::controller(DocumentController::class)->prefix('documents')->name('documents.')->group(function (): void {
        Route::get('/pending-signature', 'pendingSignature')->name('pending-signature');
        Route::get('/expiry', 'expiry')->name('expiry');
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::get('/{document}/download', 'download')->name('download');
        Route::post('/{document}/versions', 'addVersion')->name('versions.store');
        Route::post('/{document}/restore', 'restore')->withTrashed()->name('restore');
    });

    Route::resource('documents', DocumentController::class);
});
