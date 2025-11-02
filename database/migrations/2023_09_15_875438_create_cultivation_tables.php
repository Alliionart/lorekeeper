<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCultivationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cultivation_area', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name');

            $table->string('background_extension', 191)->nullable()->default(null); 
            $table->string('plot_extension', 191)->nullable()->default(null); 

            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->integer('max_plots')->unsigned();
            $table->boolean('is_active')->default(1);
            $table->integer('sort')->unsigned()->default(0);


        });

        Schema::create('cultivation_plot', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name');

            $table->string('stage_1_extension', 191)->nullable()->default(null); 
            $table->string('stage_2_extension', 191)->nullable()->default(null); 
            $table->string('stage_3_extension', 191)->nullable()->default(null); 
            $table->string('stage_4_extension', 191)->nullable()->default(null); 
            $table->string('stage_5_extension', 191)->nullable()->default(null); 

            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->boolean('is_active')->default(1);
            $table->integer('sort')->unsigned()->default(0);

        });

        Schema::create('user_plot', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('user_id')->unsigned()->index();
            $table->integer('user_area_id')->unsigned()->index(); // user area this plot is linked to
            $table->integer('plot_id')->unsigned()->index();
            $table->integer('item_id')->unsigned()->nullable()->default(null); //currently planted item
            $table->integer('stage')->unsigned(); //currently on stage 0-5
            $table->integer('plot_number')->unsigned(); //depends on max plot amount set per area
            $table->timestamp('tended_at')->nullable()->default(null); // last time the user tended to the plot
            $table->integer('counter')->unsigned(); //counts the days of tend from which the stage can be deduced
            $table->timestamps();

        });

        Schema::create('user_area', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('user_id')->unsigned()->index();
            $table->integer('area_id')->unsigned()->index();
            $table->timestamps();

        });

        Schema::create('plot_area', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('area_id')->unsigned()->index();
            $table->integer('plot_id')->unsigned()->index();
        });

        Schema::create('plot_item', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('item_id')->unsigned()->index();
            $table->integer('plot_id')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cultivation_area');
        Schema::dropIfExists('cultivation_plot');
        Schema::dropIfExists('user_plot');        
        Schema::dropIfExists('user_area');
        Schema::dropIfExists('plot_area');
        Schema::dropIfExists('plot_item');

    }
}
