<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A provider connection (M-Pesa, SMS, SMTP…) for one company, or the platform when company_id is null.
 * Credentials are encrypted at rest with APP_KEY; changing APP_KEY makes them unreadable.
 */
class Integration extends Model
{
    protected $fillable = ['company_id', 'provider', 'credentials', 'enabled', 'last_tested_at', 'last_test_ok', 'last_test_message', 'updated_by'];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'enabled' => 'boolean',
        'last_tested_at' => 'datetime',
        'last_test_ok' => 'boolean',
    ];

    protected $hidden = ['credentials'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function definition(): array
    {
        return config("integrations.{$this->provider}", []);
    }

    /** Credentials with secrets reduced to "saved / not saved", safe to send to the browser. */
    public function publicValues(): array
    {
        $out = [];
        foreach ($this->definition()['fields'] ?? [] as $key => $field) {
            $value = $this->credentials[$key] ?? null;
            $out[$key] = ! empty($field['secret']) ? null : $value;
        }

        return $out;
    }

    public function savedSecrets(): array
    {
        return collect($this->definition()['fields'] ?? [])
            ->filter(fn ($f, $key) => ! empty($f['secret']) && filled($this->credentials[$key] ?? null))
            ->keys()->all();
    }
}
