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
        Schema::create('news_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('parsed_description')->nullable();
            $table->text('discord_webhook')->nullable();
            $table->integer('sort')->default(0);
        });

        Schema::table('news', function (Blueprint $table) {
            $table->integer('category_id')->nullable()->after('parsed_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_categories');
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
    }
};
