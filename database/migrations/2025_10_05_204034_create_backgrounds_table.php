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
        Schema::create('backgrounds', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 50)->nullable();
            $table->integer('image_id')->nullable();
            $table->boolean('is_visible')->default(1);
        });
        Schema::create('background_conditions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('background_id');
            $table->string('location', 191)->nullable();
            $table->string('type', 191)->nullable();
            $table->string('value', 191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backgrounds');
        Schema::dropIfExists('background_conditions');
    }
};
