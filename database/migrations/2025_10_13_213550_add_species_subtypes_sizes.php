<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('subtypes', function (Blueprint $table) {
            $table->boolean('allow_wingspan')->default(false);
            $table->boolean('allow_height')->default(false);
            $table->string('size_data', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('subtypes', function (Blueprint $table) {
            $table->dropColumn('allow_wingspan');
            $table->dropColumn('allow_height');
            $table->dropColumn('size_data');
        });
    }
};
