<?php

namespace Database\Seeders;

use App\Models\Corridor;
use Illuminate\Database\Seeder;

class CorridorSeeder extends Seeder
{
    public function run(): void
    {
        $corridors = [
            // East Africa
            ['code' => 'KE-TZ',      'label' => 'Kenya ↔ Tanzania',                                  'group' => 'East Africa (intra-regional)', 'origin_country' => 'KE', 'destination_country' => 'TZ'],
            ['code' => 'KE-UG',      'label' => 'Kenya ↔ Uganda',                                    'group' => 'East Africa (intra-regional)', 'origin_country' => 'KE', 'destination_country' => 'UG'],
            ['code' => 'KE-RW',      'label' => 'Kenya ↔ Rwanda',                                    'group' => 'East Africa (intra-regional)', 'origin_country' => 'KE', 'destination_country' => 'RW'],
            ['code' => 'KE-ET',      'label' => 'Kenya ↔ Ethiopia',                                  'group' => 'East Africa (intra-regional)', 'origin_country' => 'KE', 'destination_country' => 'ET'],
            ['code' => 'KE-SS',      'label' => 'Kenya ↔ South Sudan',                               'group' => 'East Africa (intra-regional)', 'origin_country' => 'KE', 'destination_country' => 'SS'],
            ['code' => 'TZ-UG',      'label' => 'Tanzania ↔ Uganda',                                 'group' => 'East Africa (intra-regional)', 'origin_country' => 'TZ', 'destination_country' => 'UG'],
            ['code' => 'TZ-RW',      'label' => 'Tanzania ↔ Rwanda',                                 'group' => 'East Africa (intra-regional)', 'origin_country' => 'TZ', 'destination_country' => 'RW'],
            ['code' => 'UG-RW',      'label' => 'Uganda ↔ Rwanda',                                   'group' => 'East Africa (intra-regional)', 'origin_country' => 'UG', 'destination_country' => 'RW'],
            ['code' => 'UG-SS',      'label' => 'Uganda ↔ South Sudan',                              'group' => 'East Africa (intra-regional)', 'origin_country' => 'UG', 'destination_country' => 'SS'],
            ['code' => 'EA-MULTI',   'label' => 'Multiple East African markets',                     'group' => 'East Africa (intra-regional)'],

            // Middle East
            ['code' => 'KE-UAE',     'label' => 'Kenya ↔ UAE (Dubai)',                               'group' => 'Middle East', 'origin_country' => 'KE', 'destination_country' => 'AE'],
            ['code' => 'TZ-UAE',     'label' => 'Tanzania ↔ UAE (Dubai)',                            'group' => 'Middle East', 'origin_country' => 'TZ', 'destination_country' => 'AE'],
            ['code' => 'UG-UAE',     'label' => 'Uganda ↔ UAE (Dubai)',                              'group' => 'Middle East', 'origin_country' => 'UG', 'destination_country' => 'AE'],
            ['code' => 'KE-SA',      'label' => 'Kenya ↔ Saudi Arabia',                              'group' => 'Middle East', 'origin_country' => 'KE', 'destination_country' => 'SA'],
            ['code' => 'KE-QA',      'label' => 'Kenya ↔ Qatar',                                     'group' => 'Middle East', 'origin_country' => 'KE', 'destination_country' => 'QA'],
            ['code' => 'KE-OM',      'label' => 'Kenya ↔ Oman',                                      'group' => 'Middle East', 'origin_country' => 'KE', 'destination_country' => 'OM'],
            ['code' => 'EA-ME',      'label' => 'East Africa ↔ Middle East (general)',               'group' => 'Middle East'],

            // Asia
            ['code' => 'KE-CN',      'label' => 'Kenya ↔ China (Guangzhou, Yiwu, Shenzhen)',         'group' => 'Asia', 'origin_country' => 'KE', 'destination_country' => 'CN'],
            ['code' => 'TZ-CN',      'label' => 'Tanzania ↔ China',                                  'group' => 'Asia', 'origin_country' => 'TZ', 'destination_country' => 'CN'],
            ['code' => 'UG-CN',      'label' => 'Uganda ↔ China',                                    'group' => 'Asia', 'origin_country' => 'UG', 'destination_country' => 'CN'],
            ['code' => 'KE-IN',      'label' => 'Kenya ↔ India (Mumbai, Delhi)',                     'group' => 'Asia', 'origin_country' => 'KE', 'destination_country' => 'IN'],
            ['code' => 'TZ-IN',      'label' => 'Tanzania ↔ India',                                  'group' => 'Asia', 'origin_country' => 'TZ', 'destination_country' => 'IN'],
            ['code' => 'UG-IN',      'label' => 'Uganda ↔ India',                                    'group' => 'Asia', 'origin_country' => 'UG', 'destination_country' => 'IN'],
            ['code' => 'KE-TR',      'label' => 'Kenya ↔ Turkey',                                    'group' => 'Asia', 'origin_country' => 'KE', 'destination_country' => 'TR'],
            ['code' => 'KE-PK',      'label' => 'Kenya ↔ Pakistan',                                  'group' => 'Asia', 'origin_country' => 'KE', 'destination_country' => 'PK'],
            ['code' => 'KE-JP',      'label' => 'Kenya ↔ Japan',                                     'group' => 'Asia', 'origin_country' => 'KE', 'destination_country' => 'JP'],
            ['code' => 'KE-KR',      'label' => 'Kenya ↔ South Korea',                               'group' => 'Asia', 'origin_country' => 'KE', 'destination_country' => 'KR'],
            ['code' => 'EA-AS',      'label' => 'East Africa ↔ Asia (general)',                      'group' => 'Asia'],

            // Europe
            ['code' => 'KE-UK',      'label' => 'Kenya ↔ United Kingdom',                            'group' => 'Europe', 'origin_country' => 'KE', 'destination_country' => 'GB'],
            ['code' => 'KE-EU',      'label' => 'Kenya ↔ Europe (EU)',                               'group' => 'Europe', 'origin_country' => 'KE'],
            ['code' => 'TZ-EU',      'label' => 'Tanzania ↔ Europe (EU)',                            'group' => 'Europe', 'origin_country' => 'TZ'],
            ['code' => 'UG-EU',      'label' => 'Uganda ↔ Europe (EU)',                              'group' => 'Europe', 'origin_country' => 'UG'],
            ['code' => 'KE-DE',      'label' => 'Kenya ↔ Germany',                                   'group' => 'Europe', 'origin_country' => 'KE', 'destination_country' => 'DE'],
            ['code' => 'KE-NL',      'label' => 'Kenya ↔ Netherlands',                               'group' => 'Europe', 'origin_country' => 'KE', 'destination_country' => 'NL'],
            ['code' => 'KE-IT',      'label' => 'Kenya ↔ Italy',                                     'group' => 'Europe', 'origin_country' => 'KE', 'destination_country' => 'IT'],
            ['code' => 'KE-ES',      'label' => 'Kenya ↔ Spain',                                     'group' => 'Europe', 'origin_country' => 'KE', 'destination_country' => 'ES'],
            ['code' => 'KE-FR',      'label' => 'Kenya ↔ France',                                    'group' => 'Europe', 'origin_country' => 'KE', 'destination_country' => 'FR'],
            ['code' => 'EA-EU',      'label' => 'East Africa ↔ Europe (general)',                    'group' => 'Europe'],

            // Americas
            ['code' => 'KE-US',      'label' => 'Kenya ↔ United States',                             'group' => 'Americas', 'origin_country' => 'KE', 'destination_country' => 'US'],
            ['code' => 'KE-CA',      'label' => 'Kenya ↔ Canada',                                    'group' => 'Americas', 'origin_country' => 'KE', 'destination_country' => 'CA'],
            ['code' => 'TZ-US',      'label' => 'Tanzania ↔ United States',                           'group' => 'Americas', 'origin_country' => 'TZ', 'destination_country' => 'US'],
            ['code' => 'UG-US',      'label' => 'Uganda ↔ United States',                             'group' => 'Americas', 'origin_country' => 'UG', 'destination_country' => 'US'],
            ['code' => 'EA-US',      'label' => 'East Africa ↔ Americas (general)',                  'group' => 'Americas'],

            // Other
            ['code' => 'OTHER',      'label' => 'Other (please specify in the brief)',               'group' => 'Other'],
        ];

        foreach ($corridors as $index => $corridor) {
            Corridor::updateOrCreate(
                ['code' => $corridor['code']],
                $corridor + ['order' => $index, 'active' => true]
            );
        }
    }
}