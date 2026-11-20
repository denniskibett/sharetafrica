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
        // What the provider kept for itself, in cents (Cloud9 takes it from a deposit, or on top of a payout). 0 = none.
        public readonly int $charge = 0,
        // The network the money really moved on behind the rail (M-PESA, PesaLink, Card, Airtel Money): its reference is $receipt
        public readonly ?string $network = null,
    ) {
        if (! in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException("Unknown rail status {$status}.");
        }
    }

    public static function pending(?string $providerReference = null): self
    {
        return new self('pending', $providerReference);
    }

    public static function succeeded(string $receipt, ?string $providerReference = null, int $charge = 0, ?string $network = null): self
    {
        return new self('succeeded', $providerReference, $receipt, null, $charge, $network);
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
