<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiWebhookDelivery extends Model
{
    protected $fillable = ['api_webhook_id', 'event', 'event_id', 'payload', 'attempts', 'status', 'last_status', 'next_attempt_at', 'delivered_at'];

    protected $casts = ['payload' => 'array', 'next_attempt_at' => 'datetime', 'delivered_at' => 'datetime'];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(ApiWebhook::class, 'api_webhook_id');
    }
}
