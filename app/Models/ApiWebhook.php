<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A business's webhook endpoint (docs/v2/INTEGRATION.md §8). The secret is encrypted at rest and signs every delivery. */
class ApiWebhook extends Model
{
    public const EVENTS = ['payment.paid', 'payment.failed', 'payment.refunded', 'payment_request.paid', 'payment_request.expired'];

    protected $fillable = ['user_id', 'environment', 'url', 'events', 'secret', 'enabled'];

    protected $casts = ['events' => 'array', 'secret' => 'encrypted', 'enabled' => 'boolean'];

    protected $hidden = ['secret'];

    public function deliveries(): HasMany
    {
        return $this->hasMany(ApiWebhookDelivery::class);
    }
}
