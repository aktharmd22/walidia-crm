<?php

declare(strict_types=1);

use App\Http\Controllers\Automation\CommunicationController;
use App\Http\Controllers\Automation\MessageTemplateController;
use App\Http\Controllers\Automation\WorkflowRuleController;
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

Route::middleware(['auth', 'two-factor'])->prefix('engine')->name('engine.')->group(function (): void {

    /* What the system says ------------------------------------------------- */
    Route::controller(MessageTemplateController::class)->prefix('message-templates')->name('message-templates.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{messageTemplate}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{messageTemplate}/preview', 'preview')->name('preview');
    });
    Route::resource('message-templates', MessageTemplateController::class)
        ->parameters(['message-templates' => 'messageTemplate']);

    /* When it says it ------------------------------------------------------ */
    Route::controller(WorkflowRuleController::class)->prefix('workflows')->name('workflows.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{workflow}/restore', 'restore')->withTrashed()->name('restore');
    });
    Route::resource('workflows', WorkflowRuleController::class)->parameters(['workflows' => 'workflow']);

    /* What it sent — a ledger, never edited -------------------------------- */
    Route::controller(CommunicationController::class)->prefix('communications')->name('communications.')->group(function (): void {
        Route::get('/export', 'export')->name('export');
    });
    Route::resource('communications', CommunicationController::class)->only(['index', 'show']);
});
