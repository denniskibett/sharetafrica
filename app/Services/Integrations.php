<?php

namespace App\Services;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\QueryException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Throwable;

/**
 * Reads provider settings from the dashboard instead of .env.
 * A company's own enabled row wins; otherwise the platform row (company_id null); otherwise nothing.
 */
class Integrations
{
    public static function find(string $provider, ?int $companyId = null): ?Integration
    {
        try {
            return Integration::where('provider', $provider)->where('enabled', true)
                ->where(fn ($q) => $q->whereNull('company_id')->when($companyId, fn ($q) => $q->orWhere('company_id', $companyId)))
                ->orderByRaw('company_id is null') // company row first
                ->first();
        } catch (QueryException) {
            return null; // before migrations run
        }
    }

    /**
     * The M-Pesa connection to use: the live one when it is on and has keys, else the sandbox.
     * Returns [Integration|null, 'live'|'sandbox'|null].
     */
    public static function mpesa(?int $companyId = null): array
    {
        $live = self::find('mpesa', $companyId);
        if ($live && filled($live->credentials['consumer_key'] ?? null)) {
            return [$live, 'live'];
        }
        $sandbox = self::find('mpesa_sandbox', $companyId);

        return $sandbox ? [$sandbox, 'sandbox'] : [null, null];
    }

    /** Providers in one group (e.g. M-Pesa live / sandbox) are exclusive: turning one on turns the others off. */
    public static function exclusive(Integration $row): void
    {
        $group = config("integrations.{$row->provider}.group");
        if (! $group || ! $row->enabled) {
            return;
        }
        $siblings = collect(config('integrations'))->filter(fn ($p, $k) => ($p['group'] ?? null) === $group && $k !== $row->provider)->keys();
        Integration::where('company_id', $row->company_id)->whereIn('provider', $siblings)->update(['enabled' => false]);
    }

    public static function credentials(string $provider, ?int $companyId = null): ?array
    {
        return self::find($provider, $companyId)?->credentials;
    }

    /** Point Laravel's mailer at the company's (or platform's) SMTP settings, if any are saved. */
    public static function applyMail(?int $companyId = null): void
    {
        $c = self::credentials('smtp', $companyId);
        if ($c && ! empty($c['host'])) {
            self::useSmtp($c);
        }
    }

    /** Point the mailer at these SMTP settings for the rest of this request. */
    public static function useSmtp(array $c): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $c['host'],
            'mail.mailers.smtp.port' => (int) ($c['port'] ?? 587),
            'mail.mailers.smtp.scheme' => ($c['encryption'] ?? '') === 'ssl' ? 'smtps' : null,
            'mail.mailers.smtp.username' => $c['username'] ?? null,
            'mail.mailers.smtp.password' => $c['password'] ?? null,
            'mail.from.address' => $c['from_address'] ?? config('mail.from.address'),
            'mail.from.name' => $c['from_name'] ?? config('mail.from.name'),
        ]);
        app('mail.manager')->forgetMailers();
    }

    /** Live check against the provider. Returns [ok, message]; never includes secrets in the message. */
    public static function test(Integration $integration): array
    {
        $c = $integration->credentials ?? [];
        $live = (config("integrations.{$integration->provider}.environment") ?? $c['environment'] ?? 'sandbox') === 'live';

        try {
            [$ok, $message] = match ($integration->provider) {
                'mpesa', 'mpesa_sandbox' => self::ok(Http::timeout(10)->withBasicAuth($c['consumer_key'] ?? '', $c['consumer_secret'] ?? '')
                    ->get(($live ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke').'/oauth/v1/generate', ['grant_type' => 'client_credentials']),
                    'Daraja accepted the consumer key and secret.'),
                'mtn_momo' => self::ok(Http::timeout(10)->withBasicAuth($c['api_user'] ?? '', $c['api_key'] ?? '')
                    ->withHeaders(['Ocp-Apim-Subscription-Key' => $c['subscription_key'] ?? ''])
                    ->post(($live ? 'https://proxy.momoapi.mtn.com' : 'https://sandbox.momodeveloper.mtn.com').'/collection/token/'),
                    'MTN MoMo issued an access token.'),
                'airtel_money' => self::ok(Http::timeout(10)->post(($live ? 'https://openapi.airtel.africa' : 'https://openapiuat.airtel.africa').'/auth/oauth2/token', [
                    'client_id' => $c['client_id'] ?? '', 'client_secret' => $c['client_secret'] ?? '', 'grant_type' => 'client_credentials',
                ]), 'Airtel Money issued an access token.'),
                'africastalking' => self::ok(Http::timeout(10)->acceptJson()->withHeaders(['apiKey' => $c['api_key'] ?? ''])
                    ->get((($c['username'] ?? '') === 'sandbox' ? 'https://api.sandbox.africastalking.com' : 'https://api.africastalking.com').'/version1/user', ['username' => $c['username'] ?? '']),
                    "Africa's Talking accepted the username and key."),
                'kenyasms' => self::ok(Http::timeout(10)->get($c['api_url'] ?? ''), 'The API address answers. Send a test SMS to confirm the key.', anyAnswer: true),
                'smtp' => self::smtp($c),
                default => [true, 'Saved. This provider has no connection test.'],
            };
        } catch (TransportExceptionInterface $e) {
            // Mail server replies (e.g. "535 Authentication failed") help and never contain the password
            [$ok, $message] = [false, 'Mail server: '.mb_substr(preg_replace('/\s+/', ' ', $e->getMessage()), 0, 180)];
        } catch (Throwable $e) {
            [$ok, $message] = [false, 'Could not reach the provider ('.class_basename($e).').'];
        }

        $integration->forceFill(['last_tested_at' => now(), 'last_test_ok' => $ok, 'last_test_message' => mb_substr($message, 0, 250)])->save();

        return [$ok, $message];
    }

    protected static function ok($response, string $success, bool $anyAnswer = false): array
    {
        if ($response->successful() || ($anyAnswer && $response->status() < 500)) {
            return [true, $success];
        }

        return [false, 'The provider refused the credentials (HTTP '.$response->status().').'];
    }

    protected static function smtp(array $c): array
    {
        $transport = new EsmtpTransport($c['host'] ?? '', (int) ($c['port'] ?? 587), ($c['encryption'] ?? '') === 'ssl');
        if (! empty($c['username'])) {
            $transport->setUsername($c['username']);
            $transport->setPassword($c['password'] ?? '');
        }
        $transport->start();
        $transport->stop();

        return [true, 'Signed in to the mail server.'];
    }
}
