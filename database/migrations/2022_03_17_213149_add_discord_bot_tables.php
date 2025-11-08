<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscordBotTables extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('user_aliases', function (Blueprint $table) {
            $table->string('extra_data')->nullable()->default(null);
        });

        Schema::create('user_discord_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('exp')->default(0);
            $table->integer('level')->default(0);
            $table->timestamp('last_message_at')->nullable()->default(null); // this is only for valid, exp earning messages
        });

        Schema::create('discord_rewards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('level')->unsigned();
            $table->text('loot')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::table('user_aliases', function (Blueprint $table) {
            $table->dropColumn('extra_data');
        });
        Schema::dropIfExists('user_discord_levels');
        Schema::dropIfExists('discord_rewards');
    }
}
