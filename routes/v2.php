<?php

use App\Http\Controllers\LedgerController;
use App\Http\Controllers\PayController;
use App\Http\Controllers\ReconciliationController;
use Illuminate\Support\Facades\Route;

// v2 money platform. Loaded by App\Providers\V2ServiceProvider inside the web middleware group.

Route::middleware(['auth', 'permission:admin.reconciliation'])
    ->prefix('ledger')->name('ledger.')->controller(LedgerController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('accounts/{account}', 'show')->name('show')->whereNumber('account');
        Route::get('journals/{journal}', 'journal')->name('journal');
    });

// Reconciliation (fills the menu's existing ops.reconciliation link)
Route::middleware(['auth', 'permission:admin.reconciliation'])
    ->prefix('ops/reconciliation')->name('ops.reconciliation.')->controller(ReconciliationController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('run', 'run')->name('run');
        Route::get('{day}', 'show')->name('show')->whereNumber('day');
        Route::post('{day}/prepare', 'prepare')->name('prepare')->whereNumber('day');
        Route::post('{day}/sign-off', 'signOff')->name('sign-off')->whereNumber('day');
        Route::post('lines/{line}/fix', 'fix')->name('fix')->whereNumber('line');
        Route::post('lines/{line}/park', 'park')->name('park')->whereNumber('line');
    });

// Personal transactions: history page + the dashboard money panel's JSON (personal accounts first; business on hold)
Route::middleware(['auth', 'throttle:60,1'])
    ->prefix('pay')->name('pay.')->controller(PayController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('chart', 'chart')->name('chart');
        Route::post('quote', 'quote')->name('quote')->middleware('throttle:20,1');
        Route::post('top-up', 'topUp')->name('top-up')->middleware('throttle:20,1');
        Route::post('transfer', 'transfer')->name('transfer')->middleware('throttle:20,1');
        Route::post('pin', 'pin')->name('pin')->middleware('throttle:10,1');
        Route::post('{order}/confirm', 'confirm')->name('confirm')->middleware('throttle:10,1');
        Route::get('{order}/receipt', 'receipt')->name('receipt');
        Route::get('{order}', 'show')->name('show');
    });
