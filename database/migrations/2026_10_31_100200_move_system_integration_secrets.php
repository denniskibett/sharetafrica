<?php

use App\Models\Integration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * system.settings.integrations held the SMTP password and Google keys as plain JSON.
 * Move any filled values into encrypted platform integrations, then delete them from settings.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('system')->get() as $system) {
            $settings = json_decode($system->settings ?? '[]', true) ?: [];
            $old = $settings['integrations'] ?? null;
            if (! is_array($old)) {
                continue;
            }

            if (filled($old['mail_host'] ?? null)) {
                Integration::firstOrCreate(['company_id' => null, 'provider' => 'smtp'], ['enabled' => true, 'credentials' => [
                    'host' => $old['mail_host'],
                    'port' => $old['mail_port'] ?? 587,
                    'encryption' => 'tls',
                    'username' => $old['mail_username'] ?? null,
                    'password' => $old['mail_password'] ?? null,
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name'),
                ]]);
            }

            if (filled($old['google_maps_key'] ?? null) || filled($old['google_analytics'] ?? null)) {
                Integration::firstOrCreate(['company_id' => null, 'provider' => 'google'], ['enabled' => true, 'credentials' => [
                    'maps_key' => $old['google_maps_key'] ?? null,
                    'analytics_id' => $old['google_analytics'] ?? null,
                ]]);
            }

            unset($settings['integrations']);
            DB::table('system')->where('id', $system->id)->update(['settings' => json_encode($settings)]);
        }
    }

    public function down(): void
    {
        // Secrets stay encrypted in `integrations`; not written back as plain text.
    }
};
