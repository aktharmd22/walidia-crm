<?php

declare(strict_types=1);

use App\Http\Controllers\Crm\ClientController;
use App\Http\Controllers\Crm\CompanyController;
use App\Http\Controllers\Crm\DealController;
use App\Http\Controllers\Crm\LeadController;
use App\Http\Controllers\Crm\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CRM core
|--------------------------------------------------------------------------
|
| Leads, clients, companies, deals and tasks. Every resource here carries the
| full CRUD set plus bulk, export, import, archive and restore (D-018), each
| behind its policy. Named routes come before resource routes so /clients/vip
| is not swallowed by /clients/{client}.
|
*/

Route::middleware(['auth', 'two-factor'])->group(function (): void {

    /* Leads --------------------------------------------------------------- */
    Route::controller(LeadController::class)->prefix('leads')->name('leads.')->group(function (): void {
        Route::get('/inbox', 'inbox')->name('inbox');
        Route::get('/unassigned', 'unassigned')->name('unassigned');
        Route::get('/follow-up', 'followUp')->name('follow-up');
        Route::get('/duplicates', 'duplicates')->name('duplicates');
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{lead}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{lead}/assign', 'assign')->name('assign');
        Route::post('/{lead}/log-contact', 'logContact')->name('log-contact');
        Route::post('/{lead}/qualify', 'qualify')->name('qualify');
        Route::post('/{lead}/convert', 'convert')->name('convert');
        Route::post('/{lead}/merge', 'merge')->name('merge');
    });
    Route::resource('leads', LeadController::class);

    /* Clients ------------------------------------------------------------- */
    Route::controller(ClientController::class)->prefix('clients')->name('clients.')->group(function (): void {
        Route::get('/vip', 'vip')->name('vip');
        Route::get('/buyers', fn (Request $request) => app(ClientController::class)->ofType($request, 'buyer'))->name('buyers');
        Route::get('/sellers', fn (Request $request) => app(ClientController::class)->ofType($request, 'seller'))->name('sellers');
        Route::get('/owners', fn (Request $request) => app(ClientController::class)->ofType($request, 'owner'))->name('owners');
        Route::get('/partners', [CompanyController::class, 'partners'])->name('partners');
        Route::get('/approval-queue', 'approvalQueue')->name('approval-queue');
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{client}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{client}/approve', 'approve')->name('approve');
        Route::post('/{client}/kyc', 'verifyKyc')->name('kyc');
        Route::get('/{client}/timeline', 'timeline')->name('timeline');
    });
    Route::resource('clients', ClientController::class);

    /* Companies ----------------------------------------------------------- */
    Route::controller(CompanyController::class)->prefix('companies')->name('companies.')->group(function (): void {
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{company}/restore', 'restore')->withTrashed()->name('restore');
    });
    Route::resource('companies', CompanyController::class);

    /* Deals and the pipeline board ---------------------------------------- */
    Route::controller(DealController::class)->prefix('deals')->name('deals.')->group(function (): void {
        Route::get('/board', 'board')->name('board');
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{deal}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{deal}/stage', 'moveStage')->name('stage');
    });
    Route::resource('deals', DealController::class);

    /* Tasks --------------------------------------------------------------- */
    Route::controller(TaskController::class)->prefix('tasks')->name('tasks.')->group(function (): void {
        Route::get('/team', 'team')->name('team');
        Route::get('/overdue', 'overdue')->name('overdue');
        Route::get('/archive', 'archive')->name('archive');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk', 'bulk')->name('bulk');
        Route::post('/{task}/restore', 'restore')->withTrashed()->name('restore');
        Route::post('/{task}/complete', 'complete')->name('complete');
        Route::post('/{task}/reopen', 'reopen')->name('reopen');
        Route::post('/{task}/escalate', 'escalate')->name('escalate');
    });
    Route::resource('tasks', TaskController::class);

    /* The pipeline board is also reachable from the dashboard nav. */
    Route::get('/dashboard/pipeline', [DealController::class, 'board'])->name('dashboard.pipeline');
});
