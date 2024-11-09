<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPageSections extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        // Page categories are for displaying pages on the World/Encyclopedia page
        Schema::create('site_page_sections', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('key');
            $table->integer('sort')->unsigned()->default(0);
        });

        //link site_pages to page_categories
        Schema::table('site_page_categories', function (Blueprint $table) {
            $table->integer('section_id')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('site_page_sections');

        Schema::table('site_page_categories', function (Blueprint $table) {
            $table->dropColumn('section_id');
        });
    }
}
