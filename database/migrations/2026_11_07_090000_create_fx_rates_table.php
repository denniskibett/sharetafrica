<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** One reference rate per pair per day (Safaricom's M-PESA rate), as recorded by treasury. Never edited: a correction is a new row with a later recorded_at. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fx_rates', function (Blueprint $t) {
            $t->id();
            $t->string('pair', 7);                    // BASE/QUOTE, e.g. KES/UGX = UGX per 1 KES
            $t->decimal('rate', 20, 8);
            $t->date('rate_date');
            $t->string('source', 32)->default('safaricom');
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('note', 190)->nullable();
            $t->timestamps();
            $t->index(['pair', 'rate_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fx_rates');
    }
};
