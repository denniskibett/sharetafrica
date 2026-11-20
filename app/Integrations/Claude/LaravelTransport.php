<?php

namespace App\Integrations\Claude;

use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-18 client for the Anthropic SDK that sends through Laravel's HTTP client, so every call to Anthropic shows up
 * in Http::fake / Http::assertSent in tests and shares the app's timeouts. The SDK still builds and parses requests.
 */
class LaravelTransport implements ClientInterface
{
    public function __construct(private int $timeout = 90) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $headers = collect($request->getHeaders())->map(fn ($v) => implode(', ', $v))->except(['Host', 'Content-Length'])->all();
        $pending = Http::timeout($this->timeout)->withHeaders($headers);
        $body = (string) $request->getBody();
        if ($body !== '') {
            $pending = $pending->withBody($body, $request->getHeaderLine('Content-Type') ?: 'application/json');
        }
        try {
            return $pending->send($request->getMethod(), (string) $request->getUri())->toPsrResponse();
        } catch (ConnectionException $e) {
            throw new ConnectException($e->getMessage(), $request, $e);
        }
    }
}
