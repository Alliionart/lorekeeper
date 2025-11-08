<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleRewardId extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('discord_rewards', function (Blueprint $table) {
            $table->string('role_reward_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::table('discord_rewards', function (Blueprint $table) {
            $table->dropColumn('role_reward_id');
        });
    }
}
