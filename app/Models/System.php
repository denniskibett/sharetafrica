<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    use HasFactory;

    protected $table = 'system';

    protected $fillable = [
        'name',
        'logo',
        'logo_dark',
        'logo_icon',
        'favicon',
        'slogan',
        'timezone',
        'date_format',
        'time_format',
        'currency',
        'currency_symbol',
        'primary_color',
        'secondary_color',
        'contact_email',
        'contact_phone',
        'address',
        'location',
        'meta_description',
        'meta_keywords',
        'maintenance_mode',
        'pagination_limit',
        'custom_css',
        'custom_js',
        'settings',
        'website_pages',
        'social_media',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
        'pagination_limit' => 'integer',
        'location' => 'array',
        'settings' => 'array',
        'website_pages' => 'array',
        'social_media' => 'array',
    ];

    protected $attributes = [
        'name' => 'Sharet Africa',
        'slogan' => 'One wallet. Multiple rails. One African payment layer.',

        'timezone' => 'Africa/Nairobi',
        'date_format' => 'd M Y',
        'time_format' => 'H:i',

        'currency' => 'KES',
        'currency_symbol' => 'KSh',

        'primary_color' => '#D4FF3D',
        'secondary_color' => '#0A0A0C',

        'contact_email' => 'hello@sharet.africa',
        'contact_phone' => '+254 700 000 000',
        'address' => 'The Atrium, 1st Floor, Nairobi, Kenya',

        'meta_description' => "Cheaper payments across East Africa. Any rail. The other person doesn't need Sharet.",
        'meta_keywords' => 'Sharet, Africa, payments, M-PESA, MTN, Airtel, Vodacom, mobile money, wallet, fintech, East Africa',

        'maintenance_mode' => false,
        'pagination_limit' => 25,
    ];

    /**
     * Get the system settings record.
     */
    public static function settings(): self
    {
        return self::firstOrCreate([], [
            'name' => 'Sharet Africa',
            'slogan' => 'One wallet. Multiple rails. One African payment layer.',
            'timezone' => 'Africa/Nairobi',
            'date_format' => 'd M Y',
            'time_format' => 'H:i',
            'currency' => 'KES',
            'currency_symbol' => 'KSh',
            'primary_color' => '#D4FF3D',
            'secondary_color' => '#0A0A0C',
            'contact_email' => 'hello@sharet.africa',
            'contact_phone' => '+254 700 000 000',
            'address' => 'The Atrium, 1st Floor, Nairobi, Kenya',
            'meta_description' => "Cheaper payments across East Africa. Any rail. The other person doesn't need Sharet.",
            'pagination_limit' => 25,
        ]);
    }

    /**
     * Get a nested system setting.
     */
    public function setting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Get a website page.
     */
    public function page(string $key)
    {
        return data_get($this->website_pages, $key);
    }

    /**
     * Get enabled social links sorted by order.
     */
    public function enabledSocialLinks()
    {
        return collect($this->social_media ?? [])
            ->filter(fn ($item) => !empty($item['enabled']))
            ->sortBy('order')
            ->values();
    }

    /**
     * Get enabled website pages sorted by order.
     */
    public function menuPages()
    {
        return collect($this->website_pages ?? [])
            ->filter(fn ($page) =>
                !empty($page['enabled']) &&
                !empty($page['show_in_menu'])
            )
            ->sortBy('order')
            ->values();
    }
}