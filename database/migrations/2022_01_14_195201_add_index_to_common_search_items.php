<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCommonSearchItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // TRAITS
        Schema::table('features', function (Blueprint $table) {
            $table->index('name');
            $table->index('description');
        });
        Schema::table('feature_categories', function (Blueprint $table) {
            $table->index('name');
            $table->index('description');
        });
        Schema::table('specieses', function (Blueprint $table) {
            $table->index('name');
            $table->index('description');
        });
        Schema::table('subtypes', function (Blueprint $table) {
            $table->index('name');
            $table->index('description');
        });
        // ITEMS
        Schema::table('items', function (Blueprint $table) {
            $table->index('name');
            $table->index('description');
        });
        Schema::table('item_categories', function (Blueprint $table) {
            $table->index('name');
            $table->index('description');
        });
        Schema::table('currencies', function (Blueprint $table) {
            $table->index('name');
            $table->index('description');
        });
        // PROMPTS
        Schema::table('prompts', function (Blueprint $table) {
            $table->index('name');
            $table->index('description');
        });
        Schema::table('prompt_categories', function (Blueprint $table) {
            $table->index('name');
            $table->index('description');
        });
        // PAGES
        Schema::table('site_pages', function (Blueprint $table) {
            $table->index('title');
            $table->index('text');
        });
        Schema::table('news', function (Blueprint $table) {
            $table->index('title');
            $table->index('text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // TRAITS
        Schema::table('features', function (Blueprint $table) {
            $table->dropIndex('name');
            $table->dropIndex('description');
        });
        Schema::table('feature_categories', function (Blueprint $table) {
            $table->dropIndex('name');
            $table->dropIndex('description');
        });
        Schema::table('specieses', function (Blueprint $table) {
            $table->dropIndex('name');
            $table->dropIndex('description');
        });
        Schema::table('subtypes', function (Blueprint $table) {
            $table->dropIndex('name');
            $table->dropIndex('description');
        });
        // ITEMS
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('name');
            $table->dropIndex('description');
        });
        Schema::table('item_categories', function (Blueprint $table) {
            $table->dropIndex('name');
            $table->dropIndex('description');
        });
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropIndex('name');
            $table->dropIndex('description');
        });
        // PROMPTS
        Schema::table('prompts', function (Blueprint $table) {
            $table->dropIndex('name');
            $table->dropIndex('description');
        });
        Schema::table('prompt_categories', function (Blueprint $table) {
            $table->dropIndex('name');
            $table->dropIndex('description');
        });
        // PAGES
        Schema::table('site_pages', function (Blueprint $table) {
            $table->dropIndex('title');
            $table->dropIndex('text');
        });
        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex('title');
            $table->dropIndex('text');
        });
    }
}
