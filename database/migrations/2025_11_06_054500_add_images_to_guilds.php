<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations. // MIGRATED TO BASE
     */
    public function up(): void {
        Schema::table('guilds', function (Blueprint $table) {
            $table->boolean('has_logo')->default(0);
            $table->boolean('has_banner')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('guilds', function (Blueprint $table) {
            Schema::dropColumn('has_logo');
            Schema::dropColumn('has_banner');
        });
    }
};
