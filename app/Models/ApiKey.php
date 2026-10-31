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
    protected $fillable = ['company_id', 'user_id', 'name', 'environment', 'prefix', 'last_four', 'hash', 'enabled', 'last_used_at'];

    protected $casts = ['enabled' => 'boolean', 'last_used_at' => 'datetime'];

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

    public static function findByPlain(?string $plain): ?self
    {
        return $plain ? static::where('hash', hash('sha256', $plain))->first() : null;
    }

    public function masked(): string
    {
        return $this->prefix.str_repeat('•', 10).$this->last_four;
    }
}
