<?php

namespace App\Console\Commands;

use App\Integrations\Sms\Sms;
use Illuminate\Console\Command;

/**
 * After the go-live SMS step (docs/v2/NEXT.md): php artisan sharet:sms-test
 * Sends one real SMS through KenyaSMS as SHARETENT to SMS_TEST_TO (0717492048), never to a customer. Never prints the key.
 */
class SharetSmsTest extends Command
{
    protected $signature = 'sharet:sms-test';

    protected $description = 'Send one test SMS through KenyaSMS (SHARETENT) to SMS_TEST_TO';

    public function handle(): int
    {
        [$ok, $message] = Sms::test();
        $ok ? $this->info($message) : $this->error($message);

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
