<?php

namespace App\Integrations\Sms;

/**
 * The one way Sharet sends an SMS: KenyaSMS first (sender SHARETENT), Africa's Talking as the backup when KenyaSMS is
 * off or clearly refused the message. When KenyaSMS gave no answer (timeout, 5xx) the message may already be on its
 * way, so the backup does not send it again: a missed SMS is better than a doubled money message.
 */
class Sms
{
    /** $phone 2547…; $key makes a KenyaSMS retry send once. Returns the gateway that took it, or null. */
    public static function send(string $phone, string $message, ?string $key = null): ?string
    {
        $kenya = KenyaSms::deliver($phone, $message, $key);
        if ($kenya === 'sent') {
            return 'kenyasms';
        }
        if (in_array($kenya, ['off', 'refused'], true) && AfricasTalking::send($phone, $message)) {
            return 'africastalking';
        }

        return null;
    }

    /** Integrations › Send test SMS: always to config('sms.test_to'), never to a customer. */
    public static function test(string $provider): array
    {
        $to = self::msisdn((string) config('sms.test_to'));
        if (! $to) {
            return [false, 'SMS_TEST_TO is not a Kenyan mobile number.'];
        }
        $text = 'Sharet test SMS via '.($provider === 'africastalking' ? "Africa's Talking" : 'KenyaSMS').' at '.now('Africa/Nairobi')->format('H:i').'.';
        $ok = $provider === 'africastalking' ? AfricasTalking::send($to, $text) : KenyaSms::send($to, $text, 'sms-test:'.now()->timestamp);

        return [$ok, $ok ? 'Test SMS sent to 0'.substr($to, 3).'.' : 'The gateway did not accept the test SMS. Check the keys and the sender ID.'];
    }

    /** 2547…/2541… from 07…, 01…, +254…, or null. */
    public static function msisdn(string $phone): ?string
    {
        $d = preg_replace('/\D/', '', $phone);
        $d = preg_replace('/^0(?=[17]\d{8}$)/', '254', $d);

        return preg_match('/^254[17]\d{8}$/', $d) ? $d : null;
    }
}
