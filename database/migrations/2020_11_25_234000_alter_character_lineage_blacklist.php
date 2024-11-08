<?php

use Illuminate\Database\Migrations\Migration;

class AlterCharacterLineageBlacklist extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        DB::statement("ALTER TABLE `character_lineage_blacklist` CHANGE `type` `type` ENUM('category','species','subtype','rarity')");
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        DB::statement("ALTER TABLE `character_lineage_blacklist` CHANGE `type` `type` ENUM('category','species','subtype')");
    }
}
