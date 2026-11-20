<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * v2 ledger (docs/v2/LEDGER.md). New tables only, so v1 keeps running on the same database.
 * Journals and postings are insert-only: triggers refuse UPDATE and DELETE, whoever runs them.
 * On MySQL with binary logging, creating triggers needs SUPER or log_bin_trust_function_creators=1.
 */
return new class extends Migration
{
    private const LOCKED = ['ledger_journals', 'ledger_postings'];

    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $t) {
            $t->id();
            $t->string('code', 32);
            $t->string('name');
            $t->string('type', 16);            // asset | liability | equity | income | expense
            $t->string('book', 16);            // rail | customer | gl
            $t->char('currency', 3);
            $t->string('owner_key', 64)->default('system');
            $t->nullableMorphs('owner');
            $t->boolean('allow_negative')->default(false);
            $t->boolean('is_active')->default(true);
            $t->bigInteger('balance')->default(0);   // sum of postings, cents (debits +, credits -)
            $t->bigInteger('held')->default(0);      // open holds, cents, in the account's natural direction
            $t->unsignedBigInteger('version')->default(0);
            $t->timestamps();
            $t->unique(['code', 'currency', 'owner_key']);
        });

        Schema::create('ledger_chain', function (Blueprint $t) {
            $t->unsignedTinyInteger('id')->primary();
            $t->char('last_hash', 64)->nullable();
            $t->unsignedBigInteger('last_journal_id')->nullable();
            $t->timestamp('updated_at')->nullable();
        });
        DB::table('ledger_chain')->insert(['id' => 1]);

        Schema::create('ledger_journals', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->string('idempotency_key', 191)->unique();
            $t->char('payload_hash', 64);
            $t->string('type', 64);
            $t->char('currency', 3);
            $t->string('memo')->nullable();
            $t->string('source_type', 64)->nullable();
            $t->unsignedBigInteger('source_id')->nullable();
            $t->unsignedBigInteger('reverses_id')->nullable()->unique();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('approved_by')->nullable();
            $t->json('meta')->nullable();
            $t->char('prev_hash', 64)->nullable();
            $t->char('hash', 64);
            $t->dateTime('posted_at', 6);
            $t->index(['source_type', 'source_id']);
            $t->index(['type', 'posted_at']);
        });

        Schema::create('ledger_postings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('journal_id')->constrained('ledger_journals')->restrictOnDelete();
            $t->foreignId('account_id')->constrained('ledger_accounts')->restrictOnDelete();
            $t->bigInteger('amount');          // cents: debit +, credit -
            $t->char('currency', 3);
            $t->dateTime('created_at', 6);
            $t->index(['account_id', 'id']);
        });

        Schema::create('ledger_holds', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->foreignId('account_id')->constrained('ledger_accounts')->restrictOnDelete();
            $t->unsignedBigInteger('amount');
            $t->string('reference', 191)->unique();
            $t->string('reason')->nullable();
            $t->string('status', 16)->default('open');   // open | captured | released
            $t->dateTime('expires_at')->nullable();
            $t->unsignedBigInteger('journal_id')->nullable();
            $t->timestamps();
            $t->index(['account_id', 'status']);
        });

        foreach (self::LOCKED as $table) {
            foreach (['UPDATE', 'DELETE'] as $event) {
                $this->trigger($table, $event);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_holds');
        Schema::dropIfExists('ledger_postings');
        Schema::dropIfExists('ledger_journals');
        Schema::dropIfExists('ledger_chain');
        Schema::dropIfExists('ledger_accounts');
    }

    private function trigger(string $table, string $event): void
    {
        $name = "{$table}_no_".strtolower($event);
        $message = "{$table} is insert-only";

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::unprepared("CREATE TRIGGER {$name} BEFORE {$event} ON {$table} FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '{$message}'"),
            'sqlite' => DB::unprepared("CREATE TRIGGER {$name} BEFORE {$event} ON {$table} BEGIN SELECT RAISE(ABORT, '{$message}'); END"),
            'pgsql' => DB::unprepared("CREATE OR REPLACE FUNCTION {$name}() RETURNS trigger AS \$\$ BEGIN RAISE EXCEPTION '{$message}'; END \$\$ LANGUAGE plpgsql; CREATE TRIGGER {$name} BEFORE {$event} ON {$table} FOR EACH ROW EXECUTE FUNCTION {$name}();"),
            default => throw new RuntimeException('The ledger needs insert-only triggers; '.DB::getDriverName().' is not supported.'),
        };
    }
};
