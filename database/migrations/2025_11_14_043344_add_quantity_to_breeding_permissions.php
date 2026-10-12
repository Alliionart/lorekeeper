<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('breeding_permissions', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('type');
            $table->integer('full_quantity')->default(1)->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('breeding_permissions', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('full_quantity');
        });
    }
};
