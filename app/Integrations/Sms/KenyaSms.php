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
        $c = Integrations::credentials('kenyasms');
        if (blank($c['api_key'] ?? null) || blank($c['sender_id'] ?? null)) {
            return false; // not set up yet
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
            if (! $res->json('success')) {
                report(new \RuntimeException('KenyaSMS refused: '.($res->json('error.code') ?? 'HTTP '.$res->status())));
            }

            return (bool) $res->json('success');
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
