<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class IndexSitePages extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index-new-search-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexes all site content for the ajax search.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        if (Schema::hasTable('site_temp_index')) {
            
            //1. FIND ALL CHARACTERS TO INDEX
            $existingCharacters = SiteDataIndex::where('type', 'character')->pluck('id');
            $characters = Character::visible()->myo(0)->whereNotIn('slug', $existingCharacters)->get();
            foreach ($character as $character) {
                SiteDataIndex::create([
                    // input all neccessary fields
                    'id' => $character->id,
                    'title' => $character->id.': '.$character->name,
                    'type'  => 'Character',
                    'indentifier' => $character->slug,
                    'desc' => $character->name
                ]);
            }

            //2. FIND ALL PAGES TO INDEX

            //3. FIND ALL USERS TO INDEX

            //4. FIND ALL ITEMS TO INDEX

            //5. FIND ALL PROMPTS TO INDEX

            //6. FIND ALL SHOPS TO INDEX


            /* 
            * After finishing the index continue to the next function 
            */

        }
        if (Schema::hasTable('site_index')) {
             /* Here is where you will complete the index. Once the main index inside of the site_temp_index table is completed
            *  1. Move all contents from the temp table to the main table (site_temp_index --> site_index)
            *  2. Dump all contents of the site_temp_index table
            *  3. Wait until the next schedule period, of which the index will fill temp again, before it dumps the content of main and then repeats step 1
            */

            
            if(!empty(DB::table('site_index')->count())){
                // 2. Duplicate data to new table
                DB::table('site_index')->each(function ($first) {
                    $moved = $first->replicate();
                    $moved->setTable('site_index');
                    $moved->save();
                });

                // 3. Dump the Temp Table
                DB::table('site_temp_index')->truncate();
             }
        }
    }
}