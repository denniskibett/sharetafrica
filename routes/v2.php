<?php

use App\Http\Controllers\LedgerController;
use Illuminate\Support\Facades\Route;

// v2 money platform. Loaded by App\Providers\V2ServiceProvider inside the web middleware group.

Route::middleware(['auth', 'permission:admin.reconciliation'])
    ->prefix('ledger')->name('ledger.')->controller(LedgerController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('accounts/{account}', 'show')->name('show')->whereNumber('account');
        Route::get('journals/{journal}', 'journal')->name('journal');
    });
