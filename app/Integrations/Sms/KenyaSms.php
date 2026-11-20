<?php

namespace App\Integrations\Sms;

use App\Services\Integrations;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * KenyaSMS (https://kenyasms.com/api-documentation): POST {api_url}/sms/send with a Bearer key. Sharet's only SMS
 * provider. Every Sharet SMS goes out on the SHARETENT account with the sender SHARETENT (config sms.kenyasms.sender_id):
 * the key is KENYASMS_KEY in .env (the same key scharge uses), else the platform KenyaSMS card's.
 * Never throws: a failed SMS is reported and stays failed (no other provider is tried); whatever it was about carries on.
 */
class KenyaSms
{
    public const DEFAULT_URL = 'https://kenyasms.com/api/v1';

    /** [api_url, api_key, sender_id] for Sharet's own SMS. The sender is always the platform's (SHARETENT). */
    public static function settings(): array
    {
        $card = Integrations::credentials('kenyasms'); // the platform card (company_id null) only
        $key = config('sms.kenyasms.api_key') ?: ($card['api_key'] ?? null); // .env (scharge's SHARETENT key) wins
        $url = ($card['api_url'] ?? null) ?: (config('sms.kenyasms.base_url') ?: self::DEFAULT_URL);

        return [rtrim($url, '/'), $key ?: null, (string) config('sms.kenyasms.sender_id', 'SHARETENT')];
    }

    public static function ready(): bool
    {
        return config('sms.default') === 'kenyasms' && filled(self::settings()[1]);
    }

    /** $phone 2547…; $key makes a retry send once (KenyaSMS idempotency_key). True when KenyaSMS accepted it. */
    public static function send(string $phone, string $message, ?string $key = null): bool
    {
        [$url, $apiKey, $sender] = self::settings();
        if (config('sms.default') !== 'kenyasms' || blank($apiKey)) {
            report(new \RuntimeException('SMS not sent: KenyaSMS is not set up (SMS_DRIVER=kenyasms and KENYASMS_KEY).'));

            return false;
        }
        try {
            $res = Http::timeout(15)->acceptJson()->withToken($apiKey)
                ->post($url.'/sms/send', array_filter([
                    'sender_id' => $sender,
                    'recipient' => $phone,
                    'message' => mb_substr($message, 0, 1600),
                    'message_type' => 'transactional',
                    'idempotency_key' => $key,
                ]));
            if ($res->json('success')) {
                return true;
            }
            report(new \RuntimeException('KenyaSMS refused: '.($res->json('error.code') ?? 'HTTP '.$res->status())));

            return false;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
