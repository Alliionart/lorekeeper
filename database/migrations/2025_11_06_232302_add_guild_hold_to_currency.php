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
        Schema::table('currencies', function (Blueprint $table) {
            $table->boolean('is_guild_owned')->after('is_character_owned')->default(0);
            $table->boolean('allow_user_to_guild')->after('is_guild_owned')->default(1);
            $table->boolean('allow_guild_to_user')->after('allow_user_to_guild')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            Schema::dropColumn('is_guild_owned');
            Schema::dropColumn('allow_user_to_guild');
            Schema::dropColumn('allow_guild_to_user');
        });
    }
};
