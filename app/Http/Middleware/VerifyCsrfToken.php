<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/sms/mpesa/callback',
        '/sms/mpesa/callback-test',
        '/api/wallet/*',
        '/payments/mpesa/*',
        '/public/invoice/*/pay',
        '/test-callback',
        '/*/mpesa/callback',
        '/*/mpesa/confirmation',
        '/*/mpesa/validation',
        '/*/mpesa/result',
        '/*/mpesa/timeout',
    ];
}