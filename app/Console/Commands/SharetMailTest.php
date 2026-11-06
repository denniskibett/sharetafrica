<?php

namespace App\Console\Commands;

use App\Mail\SharetMail;
use App\Support\Outbox;
use Illuminate\Console\Command;

/**
 * Before switching on the sign-up email code (EMAIL_CODE_ON_SIGNUP), prove email lands in the inbox:
 *   php artisan sharet:mail-test you@gmail.com
 * Sends the real code email (with a dummy code) and checks the sending domain's SPF, DKIM and DMARC records.
 * Then look in that inbox: it must be in Primary/Inbox, not Spam or Promotions. Gmail › Show original › all PASS.
 */
class SharetMailTest extends Command
{
    protected $signature = 'sharet:mail-test {to : An inbox you can open (Gmail and Outlook are the strictest)} {--dkim-selector=default : DKIM selector your mail provider uses}';

    protected $description = 'Send a test sign-up code email and check SPF, DKIM and DMARC for the sending domain';

    public function handle(): int
    {
        $from = (string) config('mail.from.address');
        $domain = substr(strrchr($from, '@') ?: '', 1);
        $this->line("Sending from <info>{$from}</info> via <info>".config('mail.default').'</info>');

        $txt = fn (string $host) => collect(@dns_get_record($host, DNS_TXT) ?: [])->pluck('txt')->implode(' | ');
        $checks = [
            ['SPF', $domain, $spf = collect(explode(' | ', $txt($domain)))->first(fn ($r) => str_starts_with($r, 'v=spf1'))],
            ['DKIM', $this->option('dkim-selector')."._domainkey.{$domain}", $dkim = $txt($this->option('dkim-selector')."._domainkey.{$domain}") ?: null],
            ['DMARC', "_dmarc.{$domain}", $dmarc = $txt("_dmarc.{$domain}") ?: null],
        ];
        $this->table(['Check', 'Record', 'Found'], array_map(fn ($c) => [$c[0], $c[1], $c[2] ? substr($c[2], 0, 80) : '<error>missing</error>'], $checks));

        $sent = Outbox::now($this->argument('to'), SharetMail::emailCode('000000'));
        $sent ? $this->info('Sent. Now open the inbox and check where it landed.') : $this->error('Not sent: see storage/logs/laravel.log.');

        $ready = $sent && $spf && $dkim && $dmarc;
        $this->newLine();
        $this->line($ready
            ? 'DNS looks right. If it is in the INBOX (not spam), set EMAIL_CODE_ON_SIGNUP=true and run php artisan config:cache.'
            : '<comment>Keep EMAIL_CODE_ON_SIGNUP=false until every check passes and the email reaches the inbox.</comment>');

        return $sent ? self::SUCCESS : self::FAILURE;
    }
}
