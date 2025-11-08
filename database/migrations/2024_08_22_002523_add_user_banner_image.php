<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->string('banner')->nullable()->default(null);
            $table->text('banner_styling')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('banner');
            $table->dropColumn('banner_styling');
        });
    }
};
