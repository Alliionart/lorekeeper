<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('characters', function (Blueprint $table) {
            $table->string('nickname')->nullable();
            $table->string('size')->nullable();
            $table->string('citizenship')->nullable();
            $table->string('bonding')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('nickname');
            $table->dropColumn('size');
            $table->dropColumn('citizenship');
            $table->dropColumn('bonding');
        });
    }
};
