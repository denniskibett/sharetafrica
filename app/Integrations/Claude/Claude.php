<?php

namespace App\Integrations\Claude;

use Anthropic\Client;
use App\Models\Integration;
use Illuminate\Database\QueryException;

/**
 * Claude (Anthropic) for Sharet staff tools. One platform key: the "Claude (Anthropic)" integration card, saved
 * encrypted in `integrations` by a sysadmin, wins over ANTHROPIC_API_KEY in .env. The model comes from the card,
 * else config('services.anthropic.model') (default claude-sonnet-5-5).
 *
 * Claude only ever advises staff: it never approves, rejects or moves money (docs/v2/KYC.md, "Second reading").
 * Requests go through Laravel's HTTP client (LaravelTransport, so Http::fake works in tests).
 */
class Claude
{
    public const PROVIDER = 'claude';

    /** The platform card (company rows are never used: this key is Sharet's, not a merchant's). */
    public static function card(): ?Integration
    {
        try {
            return Integration::whereNull('company_id')->where('provider', self::PROVIDER)->where('enabled', true)->first();
        } catch (QueryException) {
            return null; // before migrations run
        }
    }

    public static function apiKey(): ?string
    {
        return (self::card()?->credentials['api_key'] ?? null) ?: (config('services.anthropic.key') ?: null);
    }

    public static function model(): string
    {
        return (self::card()?->credentials['model'] ?? null) ?: (string) config('services.anthropic.model', 'claude-sonnet-5-5');
    }

    public static function enabled(): bool
    {
        return filled(self::apiKey());
    }

    public static function client(?string $key = null): Client
    {
        return new Client(
            apiKey: $key ?? self::apiKey() ?? '',
            requestOptions: ['transporter' => new LaravelTransport((int) config('services.anthropic.timeout', 90)), 'maxRetries' => 1],
        );
    }

    /**
     * One structured call: Claude answers with JSON matching $schema. Returns [array|null $json, ?string $error].
     * $content is the user turn (text, or image/document blocks then text).
     */
    public static function json(string $system, array $content, array $schema, int $maxTokens = 4000): array
    {
        $key = self::apiKey();
        if (! $key) {
            return [null, 'Claude is not set up: add the Anthropic key under Integrations.'];
        }
        try {
            $message = self::client($key)->beta->messages->create(
                maxTokens: $maxTokens,
                model: self::model(),
                fallbacks: 'default', // a declined request is retried on the server's fallback model
                betas: ['server-side-fallback-2026-07-01'],
                outputConfig: ['effort' => 'low', 'format' => ['type' => 'json_schema', 'schema' => $schema]],
                system: $system,
                messages: [['role' => 'user', 'content' => $content]],
            );
            if ($message->stopReason === 'refusal') {
                return [null, 'Claude declined to read this one. Check it by eye.'];
            }
            if ($message->stopReason === 'max_tokens') {
                return [null, 'Claude ran out of room before finishing. Try again.'];
            }
            foreach ($message->content as $block) {
                if ($block->type === 'text' && is_array($json = json_decode($block->text, true))) {
                    return [$json, null];
                }
            }

            return [null, 'Claude returned nothing readable.'];
        } catch (\Anthropic\Core\Exceptions\AuthenticationException) {
            return [null, 'Anthropic refused the API key. Check it under Integrations.'];
        } catch (\Anthropic\Core\Exceptions\RateLimitException) {
            return [null, 'Claude is busy right now (rate limit). Try again in a minute.'];
        } catch (\Anthropic\Core\Exceptions\APIStatusException $e) {
            report($e);

            return [null, 'Claude could not be reached ('.($e->type?->value ?? 'HTTP '.$e->status).').'];
        } catch (\Throwable $e) {
            report($e);

            return [null, 'Claude could not be reached ('.class_basename($e).').'];
        }
    }

    /** Integrations › Test connection: a tiny structured call that proves the key and the model work. Never shows the key. */
    public static function test(Integration $row): array
    {
        $key = ($row->credentials['api_key'] ?? null) ?: config('services.anthropic.key');
        if (! $key) {
            return [false, 'Add the Anthropic API key first.'];
        }
        $before = config('services.anthropic.key');
        config(['services.anthropic.key' => $key]); // test what was typed even before the card is switched on
        try {
            [$json, $error] = self::json('Reply with the requested JSON only.', [['type' => 'text', 'text' => 'Say ready.']],
                ['type' => 'object', 'additionalProperties' => false, 'required' => ['status'], 'properties' => ['status' => ['type' => 'string']]], 200);
        } finally {
            config(['services.anthropic.key' => $before]);
        }

        return $error ? [false, $error] : [true, 'Claude answered on '.self::model().'.'];
    }
}
