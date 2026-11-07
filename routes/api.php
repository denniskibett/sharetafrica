<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
| Sharet API (prefix /api). Authenticate with "Authorization: Bearer sk_test_…" from Dashboard › API keys.
*/

Route::prefix('v1')->middleware(['api.key', 'throttle:60,1'])->group(function () {
    // Check a key works: returns which key and environment it is
    Route::get('/me', function (Request $request) {
        $key = $request->attributes->get('api_key');

        return response()->json([
            'key' => $key->name,
            'environment' => $key->environment,
            'company' => $key->company?->name,
        ]);
    });

    // Accounts and quotes: nothing here moves money (docs/v2/INTEGRATION.md §1, §7, §9)
    Route::middleware('api.scope:read')->controller(\App\Http\Controllers\Api\V1\AccountsController::class)->group(function () {
        Route::get('/accounts/me', 'me')->name('api.v1.accounts.me');
        Route::get('/accounts/resolve', 'resolve')->name('api.v1.accounts.resolve');
        Route::post('/quotes', 'quote')->name('api.v1.quotes');
    });
});

/*
| Sharet mobile app API v1 (/api/mobile/v1). Device tokens, not API keys. Money routes are named api.money.* so the
| ledger freeze in SystemGuard covers them, and they need an Idempotency-Key. Docs: docs/v2/MOBILE-API.md
*/
Route::prefix('mobile/v1')->name('api.mobile.')->controller(\App\Http\Controllers\Mobile\MobileController::class)->group(function () {
    Route::get('config', 'config')->middleware('throttle:60,1')->name('config');
    Route::post('auth/login', 'login')->middleware('throttle:10,1')->name('login');

    Route::middleware([\App\Http\Middleware\AuthenticateDevice::class, 'throttle:120,1'])->group(function () {
        Route::post('auth/logout', 'logout')->name('logout');
        Route::get('devices', 'devices')->name('devices');
        Route::delete('devices/{id}', 'revokeDevice')->whereNumber('id')->name('devices.revoke');
        Route::get('me', 'me')->name('me');
        Route::get('activity', 'activity')->name('activity');
        Route::get('lookup', 'lookup')->name('lookup');
        Route::post('pin', 'pin')->name('pin');
        Route::get('orders/{uuid}', 'order')->name('order');
    });
});

Route::prefix('mobile/v1')->name('api.money.mobile.')->controller(\App\Http\Controllers\Mobile\MobileController::class)
    ->middleware([\App\Http\Middleware\AuthenticateDevice::class, 'throttle:30,1', \App\Http\Middleware\Idempotent::class])->group(function () {
        Route::post('send/sharet', 'sendSharet')->name('send-sharet');
        Route::post('quote', 'quote')->name('quote');
        Route::post('orders/{uuid}/confirm', 'confirm')->name('confirm');
        Route::post('add-money', 'addMoney')->name('add-money');
    });
