<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations. //MIGRATED TO BASE
     */
    public function up(): void {
        Schema::create('guild_ranks', function (Blueprint $table) {
            $table->id();
            $table->integer('guild_id');
            $table->string('name');
            $table->integer('level')->default(0);
            $table->integer('required_reputation')->default(0);
            $table->text('description')->nullable();
            $table->boolean('for_character')->default(1);
            $table->boolean('for_user')->default(1);
            $table->boolean('has_image')->default(0);
            $table->string('hash', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('guild_ranks');
    }
};
