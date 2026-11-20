<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Verified" now needs the automatic checks to pass or staff to approve (App\Support\Kyc::review). Two plain columns
 * so staff can list the queue and spot a reused ID without decrypting anyone. People already verified before the checks
 * keep their level (review by 'before_checks'); staff can still reject them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('kyc_id_hash', 64)->nullable()->index()->after('kyc_level');
            $t->string('kyc_review', 12)->nullable()->index()->after('kyc_id_hash');   // pending | approved | rejected
        });

        DB::table('users')->whereNotNull('kyc')->orderBy('id')->each(function ($u) {
            try {
                $kyc = (array) decrypt($u->kyc);
            } catch (\Throwable) {
                return;
            }
            $update = ['kyc_id_hash' => \App\Support\Kyc::idHash($kyc['id_number'] ?? null)];
            if (in_array($u->kyc_level, ['basic', 'standard'], true) && ! isset($kyc['review'])) {
                $basis = sha1(json_encode([
                    collect(\App\Support\Kyc::VERIFY)->map(fn ($t) => $kyc['documents'][$t]['path'] ?? null)->all(),
                    $kyc['id_number'] ?? null, $kyc['full_name'] ?? null, $kyc['date_of_birth'] ?? null,
                ]));
                $kyc['review'] = ['status' => 'approved', 'by' => 'before_checks', 'basis' => $basis, 'at' => now()->toIso8601String(), 'reason' => null, 'checks' => []];
                $update += ['kyc' => encrypt($kyc), 'kyc_review' => 'approved'];
            }
            DB::table('users')->where('id', $u->id)->update($update);
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => [$t->dropIndex(['kyc_id_hash']), $t->dropIndex(['kyc_review']), $t->dropColumn(['kyc_id_hash', 'kyc_review'])]);
    }
};
