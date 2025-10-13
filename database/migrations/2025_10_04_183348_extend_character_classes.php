<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('character_classes', function (Blueprint $table) {
            $table->string('class_type')->nullable()->default(null);
            $table->integer('parent_class_id')->nullable();
            $table->integer('ability_id')->nullable();
        });
        Schema::create('class_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('character_classes', function (Blueprint $table) {
            $table->dropColumn('parent_class_id');
            $table->dropColumn('ability_id');
            $table->dropColumn('class_type');
        });
        Schema::dropIfExists('class_types');
    }
};
