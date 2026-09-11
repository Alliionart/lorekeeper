<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.//MIGRATED TO BASE
     */
    public function up(): void {
        Schema::table('guild_users', function (Blueprint $table) {
            $table->renameColumn('rank', 'rank_id');
        });
        Schema::table('guild_characters', function (Blueprint $table) {
            $table->renameColumn('rank', 'rank_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('guild_users', function (Blueprint $table) {
            $table->renameColumn('rank_id', 'rank');
        });
        Schema::table('guild_characters', function (Blueprint $table) {
            $table->renameColumn('rank_id', 'rank');
        });
    }
};
