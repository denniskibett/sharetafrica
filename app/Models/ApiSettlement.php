<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A batch of payments made with one API call (docs/v2/INTEGRATION.md §4). */
class ApiSettlement extends Model
{
    protected $fillable = ['public_id', 'api_key_id', 'user_id', 'environment', 'status', 'items', 'item_count', 'total', 'reference', 'approved_by', 'approved_at'];

    protected $casts = ['items' => 'array', 'approved_at' => 'datetime'];

    public function payments(): HasMany
    {
        return $this->hasMany(ApiPayment::class);
    }

    public function key(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class, 'api_key_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
