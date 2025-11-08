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
        Schema::create('gear_sets', function (Blueprint $table) {
            $table->id();
            $table->text('set_name');
            $table->integer('head_gear_id')->nullable();
            $table->integer('chest_gear_id')->nullable();
            $table->integer('saddle_gear_id')->nullable();
            $table->integer('flank_gear_id')->nullable();
            $table->integer('legs_gear_id')->nullable();
            $table->text('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gear_sets');
    }
};
