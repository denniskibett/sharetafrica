<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Standard security headers on every response, and http → https on the live domain.
 * (LiteSpeed does not apply the .htaccess Header rules to PHP responses, so they are set here.)
 * Camera stays allowed for this site only: verification selfies use it.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isProduction() && ! $request->secure() && in_array($request->getHost(), ['sharet.africa', 'www.sharet.africa'], true)) {
            return redirect()->to('https://sharet.africa'.$request->getRequestUri(), 301);
        }

        $response = $next($request);
        $h = $response->headers;

        if ($request->secure()) {
            $h->set('Strict-Transport-Security', 'max-age=31536000');
        }
        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('X-Frame-Options', 'SAMEORIGIN');
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $h->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=(), usb=()');
        $h->set('Content-Security-Policy', "frame-ancestors 'self'; base-uri 'self'; object-src 'none'");
        $h->remove('X-Powered-By');

        return $response;
    }
}
