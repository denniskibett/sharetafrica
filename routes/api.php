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
});
