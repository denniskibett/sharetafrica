<?php

use App\Http\Controllers\MpesaController;
use Illuminate\Support\Facades\Route;

// M-PESA CALLBACK - NO AUTH, NO CSRF
// routes/web.php - at the very top
Route::post('/pay', [MpesaController::class, 'handleCallback'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

    