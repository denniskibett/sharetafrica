<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waiting_list_entries', function (Blueprint $table) {
            // One waiting-list entry per user
            $table->unique('user_id', 'waiting_list_entries_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('waiting_list_entries', function (Blueprint $table) {
            $table->dropUnique('waiting_list_entries_user_unique');
        });
    }
};