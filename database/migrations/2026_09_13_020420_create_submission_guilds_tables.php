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
        Schema::create('submission_guilds', function (Blueprint $table) {
            $table->id();
            $table->integer('submission_id')->unsigned()->index();
            $table->integer('guild_id')->unsigned()->index();
            $table->longtext('data')->nullable();
        });

        Schema::create('queue_submission_guilds', function (Blueprint $table) {
            $table->id();
            $table->integer('queue_submission_id')->unsigned()->index();
            $table->integer('guild_id')->unsigned()->index();
            $table->longtext('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_guilds');
        Schema::dropIfExists('queue_submission_guilds');
    }
};
