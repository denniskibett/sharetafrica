<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The payment rail switch (System › Payment rails): which partner moves each kind of payment (App\Models\RailRoute),
 * and changes to it, proposed by one person and approved by another (App\Models\RailRouteChange). No row for a kind
 * of payment = the old behaviour from config/rails.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rail_routes', function (Blueprint $t) {
            $t->id();
            $t->string('movement', 20)->unique();          // collect | payout_phone | payout_b2b | payout_bank | settlement
            $t->string('rail', 20);                         // daraja | cloud9 | simulator
            $t->string('fallback', 20)->nullable();         // tried when the primary refuses before accepting
            $t->string('audience', 10)->default('named');   // named (RAIL_COLLECT_ONLY_FOR) | everyone
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->dateTime('approved_at')->nullable();
            $t->timestamps();
        });

        Schema::create('rail_route_changes', function (Blueprint $t) {
            $t->id();
            $t->string('movement', 20)->index();
            $t->string('rail', 20);
            $t->string('fallback', 20)->nullable();
            $t->string('audience', 10);
            $t->string('reason', 300);
            $t->string('status', 10)->default('pending')->index();   // pending | approved | rejected | withdrawn
            $t->foreignId('proposed_by')->constrained('users');
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->dateTime('decided_at')->nullable();
            $t->string('decision_note', 300)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rail_route_changes');
        Schema::dropIfExists('rail_routes');
    }
};
