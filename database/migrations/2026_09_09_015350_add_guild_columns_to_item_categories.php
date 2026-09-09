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
        Schema::table('item_categories', function (Blueprint $table) {
            $table->boolean('is_guild_owned')->default(1)->after('character_limit');
            $table->unsignedInteger('guild_limit')->default(0)->after('is_guild_owned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_categories', function (Blueprint $table) {
            $table->dropColumn('is_guild_owned');
            $table->dropColumn('guild_limit');
        });
    }
};
