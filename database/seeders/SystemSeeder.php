<?php

namespace Database\Seeders;

use App\Models\System;
use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder
{
    public function run(): void
    {
        $system = System::firstOrCreate([]);

        $system->update([
            // ---------- Identity ----------
            'name' => 'Sharet Africa',
            'slogan' => 'One wallet. Multiple rails. One African payment layer.',

            // ---------- Branding ----------
            'logo' => 'branding/logo.svg',
            'logo_dark' => 'branding/logo-dark.svg',
            'logo_icon' => 'branding/logo-icon.svg',
            'favicon' => 'branding/favicon.ico',

            // ---------- Locale ----------
            'timezone' => 'Africa/Nairobi',
            'date_format' => 'd M Y',
            'time_format' => 'H:i',
            'currency' => 'KES',
            'currency_symbol' => 'KSh',

            // ---------- Brand colours ----------
            // Sharet palette — electric lime on charcoal
            'primary_color' => '#D4FF3D',      // lime
            'secondary_color' => '#0A0A0C',    // charcoal (ink)

            // ---------- Contact ----------
            'contact_email' => 'hello@sharet.africa',
            'contact_phone' => '+254 700 000 000',
            'address' => 'The Atrium, 1st Floor, Nairobi, Kenya',
            'location' => [
                'name' => 'Sharet Africa HQ',
                'country' => 'KE',
                'city' => 'Nairobi',
                'latitude' => '-1.2670',
                'longitude' => '36.8080',
            ],

            // ---------- SEO ----------
            'meta_description' => 'Cheaper payments across East Africa. Any rail. The other person doesn\'t need Sharet. One wallet, one ledger, many rails.',
            'meta_keywords' => 'sharet africa, sharet, africa, Africa, payments, M-PESA, MTN, Airtel, Vodacom, mobile money, wallet, fintech, East Africa, Kenya, Tanzania, Uganda',

            // ---------- System flags ----------
            'maintenance_mode' => false,
            'pagination_limit' => 25,

            // ---------- Custom code (empty for now) ----------
            'custom_css' => null,
            'custom_js' => null,

            // ---------- Settings (JSON) ----------
            'settings' => [
                'notifications' => [
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'sms_notifications' => true,
                    'notification_sound' => true,
                ],
                'security' => [
                    'two_factor_auth' => false,
                    'login_attempts' => 5,
                    'session_timeout' => 30,
                    'password_expiry' => 90,
                ],
                'integrations' => [
                    'google_analytics' => '',
                    'google_maps_key' => '',
                    'mail_driver' => 'smtp',
                    'mail_host' => '',
                    'mail_port' => '587',
                    'mail_username' => '',
                    'mail_password' => '',
                ],
                'backup' => [
                    'auto_backup' => true,
                    'backup_frequency' => 'daily',
                    'backup_retention' => 30,
                    'backup_to_cloud' => false,
                ],
                'company' => [
                    'website' => 'https://sharet.africa',
                    'phone' => '+254 700 000 000',
                    'email' => 'hello@sharet.africa',
                    'address' => 'The Atrium, 1st Floor, Nairobi, Kenya',
                    'about' => 'Sharet Africa - one wallet connected to every mobile money network, bank, and card scheme in East Africa.',
                    'mission' => 'Make payments cheaper, accessible, and hassle-free across every rail in Africa.',
                    'vision' => 'One wallet. Multiple rails. One African payment layer.',
                    'values' => 'Transparency · Local settlement · Africa first · Build `for` the operator',
                ],
                'branding' => [
                    'tagline' => 'Cheaper. Any rail. No signup on their side.',
                    'short_description' => 'Cheaper payments across East Africa. Any rail.',
                    'footer_text' => 'Cheaper payments across East Africa. Any rail. The other person doesn\'t need Sharet.',
                    'copyright' => '© 2026 Sharet Africa · Nairobi · Kampala · Dar es Salaam',
                ],
                'rails' => [
                    'live' => ['mpesa_ke', 'mtn_ug'],
                    'in_progress' => ['vodacom_tz'],
                    'planned' => ['airtel_tz', 'airtel_ug', 'card', 'bank_ke'],
                ],
                'credit' => [
                    'terms' => [30, 60, 90],
                    'rates' => [
                        '30d' => ['min' => 1.5, 'max' => 2.5],
                        '60d' => ['min' => 1.75, 'max' => 3.0],
                        '90d' => ['min' => 2.0, 'max' => 3.5],
                    ],
                ],
                'support' => [
                    'support_email' => 'support@sharet.africa',
                    'press_email' => 'press@sharet.africa',
                    'careers_email' => 'careers@sharet.africa',
                    'reply_target_hours' => 48,
                ],
            ],

            // ---------- Social media (JSON) ----------
            'social_media' => [
                'linkedin' => [
                    'enabled' => true,
                    'url' => 'https://www.linkedin.com/company/sharet-africa',
                    'icon' => 'ri-linkedin-fill',
                    'name' => 'LinkedIn',
                    'color' => '#0A66C2',
                    'order' => 1,
                ],
                'twitter' => [
                    'enabled' => true,
                    'url' => 'https://x.com/sharetafrica',
                    'icon' => 'ri-twitter-x-fill',
                    'name' => 'X',
                    'color' => '#000000',
                    'order' => 2,
                ],
                'instagram' => [
                    'enabled' => true,
                    'url' => 'https://www.instagram.com/sharetafrica',
                    'icon' => 'ri-instagram-fill',
                    'name' => 'Instagram',
                    'color' => '#E4405F',
                    'order' => 3,
                ],
                'youtube' => [
                    'enabled' => false,
                    'url' => '',
                    'icon' => 'ri-youtube-fill',
                    'name' => 'YouTube',
                    'color' => '#FF0000',
                    'order' => 4,
                ],
                'facebook' => [
                    'enabled' => false,
                    'url' => '',
                    'icon' => 'ri-facebook-fill',
                    'name' => 'Facebook',
                    'color' => '#1877F2',
                    'order' => 5,
                ],
                'github' => [
                    'enabled' => true,
                    'url' => 'https://github.com/sharet-africa',
                    'icon' => 'ri-github-fill',
                    'name' => 'GitHub',
                    'color' => '#181717',
                    'order' => 6,
                ],
                'whatsapp' => [
                    'enabled' => false,
                    'url' => '',
                    'icon' => 'ri-whatsapp-fill',
                    'name' => 'WhatsApp',
                    'color' => '#25D366',
                    'order' => 7,
                ],
            ],

            // ---------- Website pages (JSON) ----------
            'website_pages' => [
                'home' => [
                    'enabled' => true,
                    'title' => 'Home',
                    'slug' => '',
                    'content' => '',
                    'meta_title' => 'Sharet Africa — Cheaper payments across East Africa',
                    'meta_description' => 'Cheaper payments across East Africa. Any rail. The other person doesn\'t need Sharet.',
                    'meta_keywords' => 'sharet africa, sharet, africa, africa, payments, mpesa, mtn, airtel',
                    'show_in_menu' => true,
                    'order' => 1,
                ],
                'merchants' => [
                    'enabled' => true,
                    'title' => 'Merchants',
                    'slug' => 'merchants',
                    'content' => '',
                    'meta_title' => 'Sharet for Merchants — Accept from any rail, settle anywhere',
                    'meta_description' => 'One wallet for every rail. Receive from M-PESA, MTN, Airtel, cards, and banks.',
                    'meta_keywords' => 'sharet africa, sharet, africa, merchants, accept payments, mpesa merchant',
                    'show_in_menu' => true,
                    'order' => 2,
                ],
                'personal' => [
                    'enabled' => true,
                    'title' => 'Personal',
                    'slug' => 'personal',
                    'content' => '',
                    'meta_title' => 'Sharet Personal — Send to anyone in East Africa',
                    'meta_description' => 'Send money to anyone in East Africa. Recipient doesn\'t need Sharet.',
                    'meta_keywords' => 'sharet africa, sharet, africa, personal, send money, east africa',
                    'show_in_menu' => true,
                    'order' => 3,
                ],
                'trade' => [
                    'enabled' => true,
                    'title' => 'Trade',
                    'slug' => 'trade',
                    'content' => '',
                    'meta_title' => 'Sharet Trade — Pay abroad, finance the gap, settle local',
                    'meta_description' => 'Imports, exports, intra-Africa trade, and embedded trade finance.',
                    'meta_keywords' => 'sharet africa, sharet, africa, trade, trade finance africa, import export',
                    'show_in_menu' => true,
                    'order' => 4,
                ],
                'developers' => [
                    'enabled' => true,
                    'title' => 'Developers',
                    'slug' => 'developers',
                    'content' => '',
                    'meta_title' => 'Sharet Developers — Build on the ledger or license the stack',
                    'meta_description' => 'REST API, sandbox, webhooks, white-label WaaS.',
                    'meta_keywords' => 'sharet africa, sharet, africa, api, ledger api africa, payment api',
                    'show_in_menu' => true,
                    'order' => 5,
                ],
                'company' => [
                    'enabled' => true,
                    'title' => 'Company',
                    'slug' => 'company',
                    'content' => '',
                    'meta_title' => 'Sharet Company — Named team, one ledger, one clear bet',
                    'meta_description' => 'Founded in 2026 in Nairobi. Named founders, regulatory posture, why now.',
                    'meta_keywords' => 'sharet africa, sharet, africa, company, fintech africa, nairobi fintech',
                    'show_in_menu' => true,
                    'order' => 6,
                ],
                'contact' => [
                    'enabled' => true,
                    'title' => 'Contact',
                    'slug' => 'contact',
                    'content' => '',
                    'meta_title' => 'Sharet Contact — Send a real note, get a real reply',
                    'meta_description' => 'Onboarding, press, careers. Reply within 48 working hours.',
                    'meta_keywords' => 'sharet africa, sharet, africa, contact, payments africa contact',
                    'show_in_menu' => true,
                    'order' => 7,
                ],
                'privacy' => [
                    'enabled' => true,
                    'title' => 'Privacy Policy',
                    'slug' => 'privacy-policy',
                    'content' => '',
                    'meta_title' => 'Sharet Privacy Policy',
                    'meta_description' => 'How Sharet handles your data.',
                    'meta_keywords' => 'sharet africa, sharet, africa, privacy, policy',
                    'show_in_menu' => false,
                    'order' => 8,
                ],
                'terms' => [
                    'enabled' => true,
                    'title' => 'Terms of Service',
                    'slug' => 'terms-of-service',
                    'content' => '',
                    'meta_title' => 'Sharet Terms of Service',
                    'meta_description' => 'Terms governing your use of Sharet.',
                    'meta_keywords' => 'sharet africa, sharet, africa, terms, service',
                    'show_in_menu' => false,
                    'order' => 9,
                ],
            ],
        ]);
    }
}