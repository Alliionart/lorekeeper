<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('breeding', function (Blueprint $table) {
            $table->unsignedInteger('rerolls')->nullable()->default(null)->after('results');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('breeding', function (Blueprint $table) {
            $table->dropColumn('rerolls');
        });
    }
};
