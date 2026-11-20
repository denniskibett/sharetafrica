<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A platform acting for someone's own Sharet account, within the scopes they approved (docs/v2/INTEGRATION.md §6). */
class ApiConnection extends Model
{
    public const SCOPES = ['collect', 'payout'];

    protected $fillable = ['public_id', 'token', 'platform_user_id', 'api_key_id', 'account_user_id', 'environment', 'scopes', 'status',
        'reference', 'return_url', 'expires_at', 'approved_at', 'revoked_at'];

    protected $casts = ['scopes' => 'array', 'expires_at' => 'datetime', 'approved_at' => 'datetime', 'revoked_at' => 'datetime'];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(User::class, 'platform_user_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_user_id');
    }

    public function allows(string $scope): bool
    {
        return $scope === 'read' || in_array($scope, $this->scopes ?? [], true);
    }
}
