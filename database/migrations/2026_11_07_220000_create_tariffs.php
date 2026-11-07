<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Business tariffs (docs/v2/INTEGRATION.md §7): Table 1 = fee by movement type and amount band; Table 2 = discount by
 * the account's monthly outside volume. Versioned by effective date; staff keep them in Staff › Pricing. Version 1 holds
 * the sample figures from the integration contract (to be confirmed by the owner). Amounts in cents, rates in basis points.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('version')->unique();
            $t->dateTime('effective_from');   // dateTime: MySQL strict mode refuses a NOT NULL timestamp without a default
            $t->json('bands');            // {movement: [{up_to: cents|null, fixed: cents, percent_bp: int, cap: cents|null}, …]}
            $t->json('volume_tiers');     // [{from: cents, discount_bp: int}, …] ascending
            $t->unsignedSmallInteger('excise_bp')->default(1500);
            $t->string('notes', 500)->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::table('companies', function (Blueprint $t) {
            $t->boolean('is_group_company')->default(false)->after('verified_by');   // Sharet's own companies: priced one tier up
        });

        Schema::table('api_payments', function (Blueprint $t) {
            $t->foreignId('tariff_id')->nullable()->after('total')->constrained()->nullOnDelete();
            $t->unsignedSmallInteger('discount_bp')->nullable()->after('tariff_id');
        });

        // Version 1: mobile payouts mirror today's personal tariff (Safaricom's band charge + 20%); bank and volume tiers are the contract's samples
        $mobile = collect(config('pricing.safaricom_bands'))->map(fn ($b) => ['up_to' => $b[0], 'fixed' => (int) (intdiv($b[1] * 12 + 9, 10) / 100) * 100, 'percent_bp' => 0, 'cap' => null])->values()->all();
        $mobile[count($mobile) - 1]['up_to'] = null;
        DB::table('tariffs')->insert([
            'version' => 1,
            'effective_from' => now(),
            'bands' => json_encode([
                'sharet' => [['up_to' => null, 'fixed' => 0, 'percent_bp' => 0, 'cap' => null]],
                'collect_sharet' => [['up_to' => null, 'fixed' => 0, 'percent_bp' => 0, 'cap' => null]],
                'collect_mpesa' => [['up_to' => null, 'fixed' => 0, 'percent_bp' => 0, 'cap' => null]],
                'payout_mobile' => $mobile,
                'payout_bank' => [
                    ['up_to' => 10_000_000, 'fixed' => 5_000, 'percent_bp' => 0, 'cap' => null],
                    ['up_to' => 100_000_000, 'fixed' => 10_000, 'percent_bp' => 0, 'cap' => null],
                    ['up_to' => null, 'fixed' => 0, 'percent_bp' => 1, 'cap' => 50_000],
                ],
            ]),
            'volume_tiers' => json_encode([['from' => 0, 'discount_bp' => 0], ['from' => 100_000_100, 'discount_bp' => 2500], ['from' => 1_000_000_100, 'discount_bp' => 4000]]),
            'excise_bp' => 1500,
            'notes' => 'Sample figures from INTEGRATION.md §7, to be confirmed.',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('api_payments', fn (Blueprint $t) => [$t->dropConstrainedForeignId('tariff_id'), $t->dropColumn('discount_bp')]);
        Schema::table('companies', fn (Blueprint $t) => $t->dropColumn('is_group_company'));
        Schema::dropIfExists('tariffs');
    }
};
