<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Corridor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'label', 'code', 'group',
        'active', 'order',
        'origin_country', 'destination_country',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order'  => 'integer',
    ];

    // ------- Scopes -------

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('label');
    }

    // ------- Grouped helper for dropdowns -------

    /**
     * Returns corridors grouped by group label.
     * [
     *   'East Africa (intra-regional)' => [
     *      'KE-TZ' => 'Kenya ↔ Tanzania',
     *      ...
     *   ],
     *   ...
     * ]
     */
    public static function grouped(): array
    {
        return static::active()
            ->ordered()
            ->get()
            ->groupBy('group')
            ->map(fn ($items) => $items->pluck('label', 'code')->toArray())
            ->toArray();
    }
}