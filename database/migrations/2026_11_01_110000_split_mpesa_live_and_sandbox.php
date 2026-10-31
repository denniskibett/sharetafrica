<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * M-Pesa is now two integrations: `mpesa` (live) and `mpesa_sandbox`. Existing `mpesa` rows saved with
 * environment = sandbox move to `mpesa_sandbox`; live ones stay. Credentials are encrypted, so read them
 * through the model.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (\App\Models\Integration::where('provider', 'mpesa')->get() as $row) {
            $c = $row->credentials ?? [];
            if (($c['environment'] ?? 'sandbox') !== 'live'
                && ! \App\Models\Integration::where('company_id', $row->company_id)->where('provider', 'mpesa_sandbox')->exists()) {
                unset($c['environment']);
                $row->forceFill(['provider' => 'mpesa_sandbox', 'credentials' => $c])->save();
            }
        }
    }

    public function down(): void
    {
        DB::table('integrations')->where('provider', 'mpesa_sandbox')->update(['provider' => 'mpesa']);
    }
};
