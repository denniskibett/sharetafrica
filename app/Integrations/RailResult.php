<?php

namespace App\Integrations;

/** What a rail said: pending, succeeded, failed, or unknown (no answer: check again, never resend). */
final class RailResult
{
    public const STATUSES = ['pending', 'succeeded', 'failed', 'unknown'];

    public function __construct(
        public readonly string $status,
        public readonly ?string $providerReference = null,
        public readonly ?string $receipt = null,
        public readonly ?string $message = null,
    ) {
        if (! in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException("Unknown rail status {$status}.");
        }
    }

    public static function pending(?string $providerReference = null): self
    {
        return new self('pending', $providerReference);
    }

    public static function succeeded(string $receipt, ?string $providerReference = null): self
    {
        return new self('succeeded', $providerReference, $receipt);
    }

    public static function failed(string $message, ?string $providerReference = null): self
    {
        return new self('failed', $providerReference, null, $message);
    }

    public static function unknown(?string $message = null): self
    {
        return new self('unknown', null, null, $message);
    }

    public function final(): bool
    {
        return in_array($this->status, ['succeeded', 'failed'], true);
    }
}
