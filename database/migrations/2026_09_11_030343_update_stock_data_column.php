<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.//MIGRATED TO BASE.
     */
    public function up(): void {
        Schema::table('guild_shop_stock', function (Blueprint $table) {
            $table->text('data')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('guild_shop_stock', function (Blueprint $table) {
            //
        });
    }
};
