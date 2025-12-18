<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('submissions', function (Blueprint $table) {
            $table->json('external_characters')->nullable();
        });
        Schema::table('gallery_submissions', function (Blueprint $table) {
            $table->json('external_characters')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('external_characters');
        });
        Schema::table('gallery_submissions', function (Blueprint $table) {
            $table->dropColumn('external_characters');
        });
    }
};
