<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Sharet API key. Format: sk_live_<40 chars> / sk_test_<40 chars>. Only the SHA-256 hash is kept,
 * so a lost key cannot be shown again: it is regenerated instead.
 */
class ApiKey extends Model
{
    protected $fillable = ['company_id', 'user_id', 'name', 'environment', 'scopes', 'allowed_ips', 'prefix', 'last_four', 'hash', 'enabled', 'last_used_at'];

    /** What a key may do (docs/v2/INTEGRATION.md §2). New keys get read only unless the owner chooses more. */
    public const SCOPES = ['read', 'collect', 'payout', 'connect'];

    protected $casts = ['enabled' => 'boolean', 'last_used_at' => 'datetime', 'scopes' => 'array', 'allowed_ips' => 'array'];

    protected $hidden = ['hash'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Creates a new random secret on this (unsaved or existing) key and returns the plain key once. */
    public function issue(): string
    {
        $prefix = $this->environment === 'live' ? 'sk_live_' : 'sk_test_';
        $plain = $prefix.Str::random(40);

        $this->prefix = $prefix;
        $this->last_four = substr($plain, -4);
        $this->hash = hash('sha256', $plain);

        return $plain;
    }

    public function allows(string $scope): bool
    {
        return in_array($scope, $this->scopes ?? ['read'], true);
    }

    /** Is this caller address on the key's allow-list (single IPs or CIDR ranges)? */
    public function ipAllowed(?string $ip): bool
    {
        return $ip !== null && ! empty($this->allowed_ips) && \Symfony\Component\HttpFoundation\IpUtils::checkIp($ip, $this->allowed_ips);
    }

    /** "41.90.12.7, 196.201.0.0/16" or an array → a clean list, or null if any entry isn't an IP or range. */
    public static function parseIps(string|array|null $raw): ?array
    {
        $items = collect(is_array($raw) ? $raw : preg_split('/[\s,]+/', (string) $raw))->map(fn ($i) => trim((string) $i))->filter()->unique()->values();
        foreach ($items as $item) {
            [$addr, $bits] = array_pad(explode('/', $item, 2), 2, null);
            $v6 = filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
            if (! filter_var($addr, FILTER_VALIDATE_IP) || ($bits !== null && (! ctype_digit($bits) || (int) $bits > ($v6 ? 128 : 32) || (int) $bits < ($v6 ? 32 : 8)))) {
                return null;
            }
        }

        return $items->take(20)->all();
    }

    public function isLive(): bool
    {
        return $this->environment === 'live';
    }

    public static function findByPlain(?string $plain): ?self
    {
        return $plain ? static::where('hash', hash('sha256', $plain))->first() : null;
    }

    public function masked(): string
    {
        return $this->prefix.str_repeat('•', 10).$this->last_four;
    }
}
