<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCharacterLineageBlacklistTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        // ------------------------------------------
        // id | type     | type_id | complete_removal
        // ---|----------|---------|-----------------
        //  x | category | catID   | true (blacklist)
        //  x | species  | sID     | false (greylist)
        //  x | subtype  | stID    | true (blacklist)
        // ------------------------------------------
        // blacklist > greylist > default
        // ------------------------------------------
        Schema::create('character_lineage_blacklist', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->enum('type', ['category', 'species', 'subtype']);
            $table->integer('type_id')->unsigned();
            $table->boolean('complete_removal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('character_lineage_blacklist');
    }
}
