<?php

namespace App\Integrations\Sms;

/**
 * The one way Sharet sends an SMS: KenyaSMS on the SHARETENT account, sender SHARETENT. There is no other provider and
 * no fallback: a send that fails stays failed (reported to the log).
 */
class Sms
{
    /** $phone 2547…; $key makes a retry send once. True when KenyaSMS accepted it. */
    public static function send(string $phone, string $message, ?string $key = null): bool
    {
        return KenyaSms::send($phone, $message, $key);
    }

    /** Integrations › KenyaSMS › Send test SMS: always to config('sms.test_to'), never to a customer. */
    public static function test(): array
    {
        $to = self::msisdn((string) config('sms.test_to'));
        if (! $to) {
            return [false, 'SMS_TEST_TO is not a Kenyan mobile number.'];
        }
        $ok = KenyaSms::send($to, 'Sharet test SMS via KenyaSMS ('.config('sms.kenyasms.sender_id').') at '.now('Africa/Nairobi')->format('H:i').'.', 'sms-test:'.now()->timestamp);

        return [$ok, $ok ? 'Test SMS sent to 0'.substr($to, 3).'.' : 'KenyaSMS did not accept the test SMS. Check the key (KENYASMS_KEY or this card) and that SMS_DRIVER=kenyasms.'];
    }

    /** 2547…/2541… from 07…, 01…, +254…, or null. */
    public static function msisdn(string $phone): ?string
    {
        $d = preg_replace('/\D/', '', $phone);
        $d = preg_replace('/^0(?=[17]\d{8}$)/', '254', $d);

        return preg_match('/^254[17]\d{8}$/', $d) ? $d : null;
    }
}
