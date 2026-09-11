<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.//MIGRATED TO BASE.
     */
    public function up(): void {
        Schema::table('guild_shop_log', function (Blueprint $table) {
            $table->integer('user_id')->after('guild_id');
            $table->integer('character_id')->after('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('guild_shop_log', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('character_id');
        });
    }
};
