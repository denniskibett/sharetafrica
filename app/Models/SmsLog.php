<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    protected $table = 'sms_logs';

    protected $fillable = [
        'user_id',
        'recipient',
        'message',
        'message_type',
        'provider',
        'status',
        'provider_message_id',
        'provider_response',
        'error',
        'cost',
        'context',
        'ip_address',
        'delivered_at',
        'failed_at',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'cost'              => 'decimal:2',
        'delivered_at'      => 'datetime',
        'failed_at'         => 'datetime',
    ];

    // ==========================================================
    // Status constants — use these everywhere instead of raw strings
    // ==========================================================

    public const STATUS_QUEUED    = 'queued';
    public const STATUS_SENT      = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_SKIPPED   = 'skipped';
    public const STATUS_REJECTED  = 'rejected';

    // ==========================================================
    // Relationships
    // ==========================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==========================================================
    // Scopes — handy query shortcuts
    // ==========================================================

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}