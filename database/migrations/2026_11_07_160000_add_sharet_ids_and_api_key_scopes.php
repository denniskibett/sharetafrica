<?php

use App\Support\SharetId;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sharet IDs for every person and business (docs/v2/INTEGRATION.md §1), backfilled for existing accounts, and scopes on
 * API keys (§2). Existing keys keep working read-only until their owner gives them more scopes.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'companies'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('sharet_id', 9)->nullable()->unique()->after('id');
            });
        }
        Schema::table('api_keys', function (Blueprint $t) {
            $t->json('scopes')->nullable()->after('environment');
        });

        foreach (['users', 'companies'] as $table) {
            DB::table($table)->whereNull('sharet_id')->orderBy('id')->pluck('id')->each(
                fn ($id) => DB::table($table)->where('id', $id)->update(['sharet_id' => SharetId::generate()])
            );
        }
        DB::table('api_keys')->whereNull('scopes')->update(['scopes' => json_encode(['read'])]);
    }

    public function down(): void
    {
        Schema::table('api_keys', fn (Blueprint $t) => $t->dropColumn('scopes'));
        foreach (['users', 'companies'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropUnique(['sharet_id']);
                $t->dropColumn('sharet_id');
            });
        }
    }
};
