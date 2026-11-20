<?php

namespace App\Integrations\Sms;

use App\Services\Integrations;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Africa's Talking SMS (POST /version1/messaging, form-encoded, "apiKey" header): Sharet's backup gateway, used by
 * Sms::send when KenyaSMS is off or refuses a message. Username "sandbox" uses the AT sandbox (nothing is delivered).
 * Never throws.
 */
class AfricasTalking
{
    public static function ready(): bool
    {
        $c = Integrations::credentials('africastalking');

        return filled($c['username'] ?? null) && filled($c['api_key'] ?? null);
    }

    /** $phone 2547… Returns true when AT accepted the message for delivery. */
    public static function send(string $phone, string $message): bool
    {
        $c = Integrations::credentials('africastalking');
        if (blank($c['username'] ?? null) || blank($c['api_key'] ?? null)) {
            return false;
        }
        $host = $c['username'] === 'sandbox' ? 'https://api.sandbox.africastalking.com' : 'https://api.africastalking.com';
        try {
            $res = Http::timeout(15)->acceptJson()->asForm()->withHeaders(['apiKey' => $c['api_key']])
                ->post($host.'/version1/messaging', array_filter([
                    'username' => $c['username'],
                    'to' => '+'.ltrim($phone, '+'),
                    'message' => mb_substr($message, 0, 1600),
                    'from' => $c['sender_id'] ?? null,
                ]));
            // 100 Processed, 101 Sent, 102 Queued
            $status = (int) $res->json('SMSMessageData.Recipients.0.statusCode');
            if ($res->successful() && in_array($status, [100, 101, 102], true)) {
                return true;
            }
            report(new \RuntimeException("Africa's Talking refused: ".($res->json('SMSMessageData.Recipients.0.status') ?? 'HTTP '.$res->status())));

            return false;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
