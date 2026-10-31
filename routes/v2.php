<?php

use App\Http\Controllers\LedgerController;
use App\Http\Controllers\PayController;
use Illuminate\Support\Facades\Route;

// v2 money platform. Loaded by App\Providers\V2ServiceProvider inside the web middleware group.

Route::middleware(['auth', 'permission:admin.reconciliation'])
    ->prefix('ledger')->name('ledger.')->controller(LedgerController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('accounts/{account}', 'show')->name('show')->whereNumber('account');
        Route::get('journals/{journal}', 'journal')->name('journal');
    });

// Personal "Pay anyone" (personal accounts first; business on hold)
Route::middleware(['auth', 'throttle:60,1'])
    ->prefix('pay')->name('pay.')->controller(PayController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('quote', 'quote')->name('quote')->middleware('throttle:20,1');
        Route::post('{order}/confirm', 'confirm')->name('confirm')->middleware('throttle:10,1');
        Route::get('{order}', 'show')->name('show');
    });
