<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('breeding_log', function (Blueprint $table) {
            $table->id();
            $table->integer('breeding_id');
            $table->integer('sender_id')->nullable();
            $table->integer('parent_1_id')->nullable();
            $table->integer('parent_2_id')->nullable();
            $table->text('log')->nullable();
            $table->char('log_type', 191)->default('None');
            $table->text('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('breeding_log');
    }
};
