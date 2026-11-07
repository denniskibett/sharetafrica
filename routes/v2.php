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

// Customer app: Help (canvas A17), and read-only previews of designed features that have no backend yet
// (Requests A08, Card A11, Send abroad A13, SWIFT A12). The previews never move money and post nothing.
Route::middleware('auth')->group(function () {
    Route::view('help', 'help.index')->name('help');
    Route::view('requests', 'soon.requests')->name('requests');
    Route::view('card', 'soon.card')->name('card');
    Route::view('abroad', 'soon.abroad')->name('abroad');
    Route::view('abroad/swift', 'soon.swift')->name('abroad.swift');
});
