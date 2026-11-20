<?php

use App\Http\Controllers\LedgerController;
use App\Http\Controllers\PayController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\SupportController;
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

// Support desk: the cases queue (fills the menu's existing "Support tickets" link; support, operations, admins)
Route::middleware(['auth', 'role:support|operations|admin|super_admin'])
    ->prefix('ops/support')->name('ops.support.')->controller(SupportController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('next', 'next')->name('next');
        Route::post('{case}/assign', 'assign')->name('assign')->whereNumber('case');
        Route::post('{case}/close', 'close')->name('close')->whereNumber('case');
    });

// Personal transactions: history page + the dashboard money panel's JSON (personal accounts first; business on hold)
Route::middleware(['auth', 'throttle:60,1,pay-pages'])
    ->prefix('pay')->name('pay.')->controller(PayController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('chart', 'chart')->name('chart');
        Route::post('quote', 'quote')->name('quote')->middleware('throttle:20,1,pay-quote');
        Route::post('top-up', 'topUp')->name('top-up')->middleware('throttle:20,1,pay-top-up');
        Route::post('card', 'card')->name('card')->middleware('throttle:10,1,pay-card');
        Route::get('account-number', 'accountNumber')->name('account-number');
        Route::get('{order}/card-return', 'cardReturn')->name('card.return');
        Route::post('transfer', 'transfer')->name('transfer')->middleware('throttle:20,1,pay-transfer');
        Route::post('pin', 'pin')->name('pin')->middleware('throttle:10,1,pay-pin');
        Route::post('{order}/confirm', 'confirm')->name('confirm')->middleware('throttle:10,1,pay-confirm');
        Route::get('{order}/receipt', 'receipt')->name('receipt');
        Route::get('{order}', 'show')->name('show');
    });

// Customer app: Help (canvas A17), and read-only previews of designed features that have no backend yet
// (Requests A08, Card A11, Send abroad A13, SWIFT A12). The previews never move money and post nothing.
Route::middleware('auth')->group(function () {
    Route::view('help', 'help.index')->name('help');
    // Requests and splits (canvas P05): ask an @name or any phone; they pay on /r/{token} by M-PESA or their Sharet wallet
    Route::get('requests', [\App\Http\Controllers\RequestsController::class, 'index'])->name('requests');
    Route::middleware('throttle:30,1,requests')->controller(\App\Http\Controllers\RequestsController::class)->prefix('requests')->name('requests.')->group(function () {
        Route::get('data', 'data')->name('data');
        Route::post('/', 'store')->name('store');
        Route::post('split', 'split')->name('split');
        Route::post('{id}/remind', 'remind')->name('remind');
        Route::post('{id}/cancel', 'cancel')->name('cancel');
        Route::post('{id}/decline', 'decline')->name('decline');
    });
    Route::view('card', 'soon.card')->name('card');
    Route::view('abroad', 'soon.abroad')->name('abroad');
    Route::view('abroad/swift', 'soon.swift')->name('abroad.swift');
});
