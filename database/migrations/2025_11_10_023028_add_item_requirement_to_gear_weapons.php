<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gears', function (Blueprint $table) {
            $table->boolean('remove_item_to_remove');
        });
        Schema::table('weapons', function (Blueprint $table) {
            $table->boolean('remove_item_to_remove');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gears', function (Blueprint $table) {
            Schema::dropColumn('remove_item_to_remove');
        });
        Schema::table('weapons', function (Blueprint $table) {
            Schema::dropColumn('remove_item_to_remove');
        });
    }
};
