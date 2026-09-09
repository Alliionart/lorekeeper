<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items_log', function (Blueprint $table) {
            DB::statement("ALTER TABLE `items_log` MODIFY COLUMN `sender_type` ENUM('User', 'Character', 'Shop', 'Guild') NULL");
            DB::statement("ALTER TABLE `items_log` MODIFY COLUMN `recipient_type` ENUM('User', 'Character', 'Shop', 'Guild') NULL");
        });

        Schema::table('currencies_log', function (Blueprint $table) {
            DB::statement("ALTER TABLE `currencies_log` MODIFY COLUMN `sender_type` ENUM('User', 'Character', 'Shop', 'Guild') NULL");
            DB::statement("ALTER TABLE `currencies_log` MODIFY COLUMN `recipient_type` ENUM('User', 'Character', 'Shop', 'Guild') NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items_log', function (Blueprint $table) {
            DB::statement("ALTER TABLE `items_log` MODIFY COLUMN `sender_type` ENUM('User', 'Shop', 'Character') NULL");
            DB::statement("ALTER TABLE `items_log` MODIFY COLUMN `recipient_type` ENUM('User', 'Shop', 'Character') NULL");
        });

        Schema::table('currencies_log', function (Blueprint $table) {
            DB::statement("ALTER TABLE `currencies_log` MODIFY COLUMN `sender_type` ENUM('User', 'Shop', 'Character') NULL");
            DB::statement("ALTER TABLE `currencies_log` MODIFY COLUMN `recipient_type` ENUM('User', 'Shop', 'Character') NULL");
        });
    }
};
