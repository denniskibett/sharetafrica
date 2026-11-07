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
    protected $fillable = ['company_id', 'user_id', 'name', 'environment', 'scopes', 'prefix', 'last_four', 'hash', 'enabled', 'last_used_at'];

    /** What a key may do (docs/v2/INTEGRATION.md §2). New keys get read only unless the owner chooses more. */
    public const SCOPES = ['read', 'collect', 'payout', 'connect'];

    protected $casts = ['enabled' => 'boolean', 'last_used_at' => 'datetime', 'scopes' => 'array'];

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
