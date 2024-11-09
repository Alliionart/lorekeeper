<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCharacterLockedItems extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('item_categories', function (Blueprint $table) {
            //
            $table->boolean('is_character_locked')->default(0);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->boolean('is_character_locked')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
    }
}
