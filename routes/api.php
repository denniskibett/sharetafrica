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

    // Growth numbers for the key's own business site(s) only: the localized Growth tab in each app (config/growth.php)
    Route::get('/growth/summary', [\App\Http\Controllers\Api\V1\GrowthController::class, 'summary'])
        ->middleware('api.scope:read')->name('api.v1.growth.summary');

    // Accounts and quotes: nothing here moves money (docs/v2/INTEGRATION.md §1, §7, §9)
    Route::middleware('api.scope:read')->controller(\App\Http\Controllers\Api\V1\AccountsController::class)->group(function () {
        Route::get('/accounts/me', 'me')->name('api.v1.accounts.me');
        Route::get('/accounts/resolve', 'resolve')->name('api.v1.accounts.resolve');
        Route::post('/quotes', 'quote')->name('api.v1.quotes');
    });

    // Payments out: "payout" scope, Idempotency-Key, and api.money.* so the ledger freeze stops them (§4)
    Route::controller(\App\Http\Controllers\Api\V1\PaymentsController::class)->group(function () {
        Route::post('/payments', 'store')->middleware(['api.scope:payout', \App\Http\Middleware\Idempotent::class])->name('api.money.v1.payments.store');
        Route::get('/payments/{id}', 'show')->middleware('api.scope:read')->name('api.v1.payments.show');
    });

    // Batch settlements: many payments in one call; live batches over the threshold wait for a second approver (§4)
    Route::controller(\App\Http\Controllers\Api\V1\SettlementsController::class)->group(function () {
        Route::post('/settlements', 'store')->middleware(['api.scope:payout', \App\Http\Middleware\Idempotent::class])->name('api.money.v1.settlements.store');
        Route::get('/settlements/{id}', 'show')->middleware('api.scope:read')->name('api.v1.settlements.show');
    });

    // Payment requests: money in (§3). Creating one moves nothing; paying happens on the hosted page.
    Route::controller(\App\Http\Controllers\Api\V1\PaymentRequestsController::class)->group(function () {
        Route::post('/payment-requests', 'store')->middleware(['api.scope:collect', \App\Http\Middleware\Idempotent::class])->name('api.v1.payment_requests.store');
        Route::get('/payment-requests/{id}', 'show')->middleware('api.scope:read')->name('api.v1.payment_requests.show');
        Route::post('/payment-requests/{id}/cancel', 'cancel')->middleware('api.scope:collect')->name('api.v1.payment_requests.cancel');
        Route::post('/payment-requests/{id}/settle', 'settle')->middleware(['api.scope:payout'])->name('api.money.v1.payment_requests.settle');
    });

    // Connected accounts: the platform asks people to connect, lists and disconnects them (§6)
    Route::middleware('api.scope:connect')->controller(\App\Http\Controllers\Api\V1\ConnectController::class)->group(function () {
        Route::post('/connect/links', 'link')->name('api.v1.connect.link');
        Route::get('/connect/accounts', 'index')->name('api.v1.connect.index');
        Route::post('/connect/accounts/{id}/disconnect', 'disconnect')->name('api.v1.connect.disconnect');
    });

    // Webhook endpoints (§8)
    Route::middleware('api.scope:read')->controller(\App\Http\Controllers\Api\V1\WebhooksController::class)->group(function () {
        Route::get('/webhooks', 'index')->name('api.v1.webhooks.index');
        Route::post('/webhooks', 'store')->name('api.v1.webhooks.store');
        Route::delete('/webhooks/{id}', 'destroy')->whereNumber('id')->name('api.v1.webhooks.destroy');
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
