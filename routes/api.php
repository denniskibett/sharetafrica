<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
| Sharet API (prefix /api). Authenticate with "Authorization: Bearer sk_test_…" from Dashboard › API keys.
*/

// Reference FX rates for display (Safaricom's rate, no margin): public, read-only, nothing is quoted (docs/v2/FX.md)
Route::get('v1/fx/rates', \App\Http\Controllers\Api\V1\FxRatesController::class)->middleware('throttle:60,1,api-fx-rates')->name('api.v1.fx.rates');

Route::prefix('v1')->middleware(['api.key', 'throttle:60,1,api-v1'])->group(function () {
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
    Route::get('config', 'config')->middleware('throttle:60,1,mobile-config')->name('config');
    Route::post('auth/login', 'login')->middleware('throttle:10,1,mobile-login')->name('login');

    Route::middleware([\App\Http\Middleware\AuthenticateDevice::class, 'throttle:120,1,mobile-device'])->group(function () {
        Route::post('auth/logout', 'logout')->name('logout');
        Route::get('devices', 'devices')->name('devices');
        Route::delete('devices/{id}', 'revokeDevice')->whereNumber('id')->name('devices.revoke');
        Route::get('me', 'me')->name('me');
        Route::get('activity', 'activity')->name('activity');
        Route::get('lookup', 'lookup')->name('lookup');
        Route::post('pin', 'pin')->name('pin');
        Route::get('orders/{uuid}', 'order')->name('order');
        Route::get('account-number', 'accountNumber')->name('account-number');
        Route::get('favourites', 'favourites')->name('favourites');
        Route::post('quick-pay', 'quickPay')->middleware('throttle:10,1,mobile-quick-pay')->name('quick-pay');
        Route::post('phone-lock', 'phoneLock')->middleware('throttle:10,1,mobile-phone-lock')->name('phone-lock');
        Route::post('auth/unlock', 'unlock')->middleware('throttle:10,1,mobile-unlock')->name('unlock');
        Route::post('me/freeze', 'freeze')->name('freeze');
        Route::post('me/unfreeze', 'unfreeze')->middleware('throttle:10,1,mobile-unfreeze')->name('unfreeze');
        Route::get('requests', 'requests')->name('requests');
        Route::post('requests', 'askMoney')->middleware('throttle:20,1,mobile-requests')->name('requests.ask');
        Route::post('requests/split', 'splitBill')->middleware('throttle:10,1,mobile-requests')->name('requests.split');
        Route::post('requests/{id}/{action}', 'requestAction')->where('action', 'decline|remind|cancel')->middleware('throttle:20,1,mobile-requests')->name('requests.action');
    });
});

Route::prefix('mobile/v1')->name('api.money.mobile.')->controller(\App\Http\Controllers\Mobile\MobileController::class)
    ->middleware([\App\Http\Middleware\AuthenticateDevice::class, 'throttle:30,1,mobile-money', \App\Http\Middleware\Idempotent::class])->group(function () {
        Route::post('send/sharet', 'sendSharet')->name('send-sharet');
        Route::post('quote', 'quote')->name('quote');
        Route::post('orders/{uuid}/confirm', 'confirm')->name('confirm');
        Route::post('add-money', 'addMoney')->name('add-money');
        Route::post('add-money/card', 'addByCard')->name('add-money-card');
        Route::post('requests/{id}/pay', 'payRequest')->name('requests.pay');
    });

/*
| Business and Trade in the app (Phase 2; docs/v2/MOBILE-API.md § Business, § Trade). Preparing, rejecting and team
| changes move nothing; approving a batch and paying an invoice are money routes (Idempotency-Key, ledger freeze).
*/
Route::prefix('mobile/v1')->name('api.mobile.')->middleware([\App\Http\Middleware\AuthenticateDevice::class, 'throttle:120,1,mobile-device'])->group(function () {
    Route::controller(\App\Http\Controllers\Mobile\BusinessController::class)->prefix('business')->name('business.')->group(function () {
        Route::get('/', 'home')->name('home');
        Route::get('batches', 'batches')->name('batches');
        Route::post('batches', 'store')->middleware('throttle:20,1,mobile-business-prepare')->name('batches.store');
        Route::get('batches/{id}', 'batch')->name('batches.show');
        Route::get('batches/{id}/report', 'report')->name('batches.report');
        Route::post('batches/{id}/repeat', 'repeat')->middleware('throttle:20,1,mobile-business-prepare')->name('batches.repeat');
        Route::post('batches/{id}/reject', 'reject')->name('batches.reject');
        Route::post('batches/{id}/cancel', 'cancel')->name('batches.cancel');
        Route::get('payees', 'payees')->name('payees');
        Route::get('team', 'team')->name('team');
        Route::post('team', 'invite')->middleware('throttle:10,1,mobile-business-team')->name('team.invite');
        Route::patch('team/{id}', 'updateMember')->whereNumber('id')->middleware('throttle:10,1,mobile-business-team')->name('team.update');
        Route::delete('team/{id}', 'removeMember')->whereNumber('id')->middleware('throttle:10,1,mobile-business-team')->name('team.remove');
        Route::get('statement', 'statement')->name('statement');
    });
    Route::controller(\App\Http\Controllers\Mobile\TradeController::class)->prefix('trade')->name('trade.')->group(function () {
        Route::get('/', 'home')->name('home');
        Route::post('orders', 'store')->middleware('throttle:20,1,mobile-trade')->name('orders.store');
        Route::get('orders/{id}', 'show')->whereNumber('id')->name('orders.show');
        Route::post('orders/{id}/invoice', 'invoice')->whereNumber('id')->name('orders.invoice');
        Route::post('orders/{id}/decline', 'decline')->whereNumber('id')->name('orders.decline');
        Route::post('orders/{id}/cancel', 'cancel')->whereNumber('id')->name('orders.cancel');
        Route::post('orders/{id}/finance', 'finance')->whereNumber('id')->name('orders.finance');
        Route::post('fx-quotes', 'quote')->middleware('throttle:30,1,mobile-trade')->name('fx-quotes.store');
        Route::get('fx-quotes/{id}', 'showQuote')->name('fx-quotes.show');
    });
});

Route::prefix('mobile/v1')->name('api.money.mobile.')->middleware([\App\Http\Middleware\AuthenticateDevice::class, 'throttle:30,1,mobile-money', \App\Http\Middleware\Idempotent::class])->group(function () {
    Route::post('business/batches/{id}/approve', [\App\Http\Controllers\Mobile\BusinessController::class, 'approve'])->name('business.batches.approve');
    Route::post('trade/orders/{id}/pay', [\App\Http\Controllers\Mobile\TradeController::class, 'pay'])->whereNumber('id')->name('trade.orders.pay');
    Route::post('trade/fx-quotes/{id}/pay', [\App\Http\Controllers\Mobile\TradeController::class, 'payQuote'])->name('trade.fx-quotes.pay');
});
