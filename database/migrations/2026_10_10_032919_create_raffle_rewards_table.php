<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('raffle_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('raffle_id');
            $table->foreign('raffle_id')->references('id')->on('raffles');
            $table->string('position', 10)->nullable();
            $table->string('rewardable_type');
            $table->unsignedBigInteger('rewardable_id');
            $table->integer('quantity')->default(1);
            $table->integer('weight')->default(1);
            $table->longtext('data')->nullable();
            $table->boolean('opt_out')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('raffle_rewards');
    }
};
