<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One version of the business tariff (docs/v2/INTEGRATION.md §7). Never edited in place: a change is a new version. */
class Tariff extends Model
{
    public const MOVEMENTS = [
        'sharet' => 'Sharet ↔ Sharet',
        'collect_sharet' => 'Collect by Sharet wallet',
        'collect_mpesa' => 'Collect by M-PESA prompt',
        'payout_mobile' => 'Pay out to M-PESA, Airtel, T-Kash, tills and paybills',
        'payout_bank' => 'Pay out to a bank (PesaLink)',
    ];

    protected $fillable = ['version', 'effective_from', 'bands', 'volume_tiers', 'excise_bp', 'notes', 'created_by'];

    protected $casts = ['effective_from' => 'datetime', 'bands' => 'array', 'volume_tiers' => 'array'];

    /** The version in force now: the latest one whose effective date has come. */
    public static function current(): ?self
    {
        return static::where('effective_from', '<=', now())->orderByDesc('effective_from')->orderByDesc('version')->first();
    }
}
