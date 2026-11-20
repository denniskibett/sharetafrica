<?php

namespace App\Integrations\Sms;

use App\Services\Integrations;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * KenyaSMS (https://kenyasms.com/api-documentation): POST {api_url}/sms/send with a Bearer key. Settings come from the
 * KenyaSMS integration. Never throws: an SMS that fails is reported, and whatever it was about carries on.
 */
class KenyaSms
{
    public const DEFAULT_URL = 'https://kenyasms.com/api/v1';

    public static function ready(): bool
    {
        $c = Integrations::credentials('kenyasms');

        return filled($c['api_key'] ?? null) && filled($c['sender_id'] ?? null);
    }

    /** $phone 2547…; $key makes a retry send once (KenyaSMS idempotency_key). */
    public static function send(string $phone, string $message, ?string $key = null): bool
    {
        return self::deliver($phone, $message, $key) === 'sent';
    }

    /**
     * Like send(), but says what happened, so Sms::send knows whether a backup gateway may try:
     * 'sent', 'off' (not set up), 'refused' (KenyaSMS answered no: safe to try elsewhere) or 'unknown' (no answer:
     * it may have gone out, so nobody else sends it).
     */
    public static function deliver(string $phone, string $message, ?string $key = null): string
    {
        $c = Integrations::credentials('kenyasms');
        if (blank($c['api_key'] ?? null) || blank($c['sender_id'] ?? null)) {
            return 'off'; // not set up yet
        }
        try {
            $res = Http::timeout(15)->acceptJson()->withToken($c['api_key'])
                ->post(rtrim($c['api_url'] ?? '' ?: self::DEFAULT_URL, '/').'/sms/send', array_filter([
                    'sender_id' => $c['sender_id'],
                    'recipient' => $phone,
                    'message' => mb_substr($message, 0, 1600),
                    'message_type' => 'transactional',
                    'idempotency_key' => $key,
                ]));
            if ($res->json('success')) {
                return 'sent';
            }
            report(new \RuntimeException('KenyaSMS refused: '.($res->json('error.code') ?? 'HTTP '.$res->status())));

            return $res->serverError() ? 'unknown' : 'refused';
        } catch (Throwable $e) {
            report($e);

            return 'unknown';
        }
    }
}
