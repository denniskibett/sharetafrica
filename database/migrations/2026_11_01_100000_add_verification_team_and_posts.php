<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - users.kyc: encrypted verification details (ID, KRA PIN, date of birth, address, occupation…);
 *   users.kyc_level: basic (M-Pesa grade) | standard (bank grade) set from what was provided.
 * - team_members: the team shown on /company, edited in Website › Team.
 * - posts: the blog (rich text), edited in Website › Blog.
 * Additive only: nothing existing is changed or dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'kyc')) {
                $table->text('kyc')->nullable()->after('social');
                $table->string('kyc_level', 20)->default('none')->after('kyc');
                $table->timestamp('kyc_submitted_at')->nullable()->after('kyc_level');
            }
        });

        if (! Schema::hasTable('team_members')) {
            Schema::create('team_members', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('role');
                $table->text('bio')->nullable();
                $table->string('photo')->nullable();
                $table->string('linkedin')->nullable();
                $table->unsignedInteger('sort')->default(0);
                $table->boolean('published')->default(true);
                $table->timestamps();
            });

            $now = now();
            DB::table('team_members')->insert([
                ['name' => 'Dennis Kibett', 'role' => 'Co-founder · CEO', 'sort' => 1, 'published' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Paul Pennuel', 'role' => 'CTO', 'sort' => 2, 'published' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'David Kirui', 'role' => 'Senior Engineer · Mobile', 'sort' => 3, 'published' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (! Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('excerpt', 300)->nullable();
                $table->longText('body')->nullable();
                $table->string('cover')->nullable();
                $table->string('status', 20)->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('team_members');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['kyc', 'kyc_level', 'kyc_submitted_at']));
    }
};
