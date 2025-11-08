<?php

namespace App\Console\Commands;

use App\Models\User\UserAlias;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDiscord extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-discord';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates user aliases to drop extra_data column and add information to user_snowflake, if applicable.';

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
        if (Schema::hasColumn('user_aliases', 'extra_data')) {
            $aliases = UserAlias::whereNotNull('extra_data')->where('site', 'discord')->get();

            // progress bar
            $bar = $this->output->createProgressBar(count($aliases));
            $bar->start();
            foreach ($aliases as $alias) {
                $this->line('Updating user_snowflake for '.$alias->alias.'...');
                $alias->user_snowflake = $alias->extra_data;
                $alias->save();

                $bar->advance();
            }
            $bar->finish();

            $this->line('Dropping extra_data column...');
            Schema::table('user_aliases', function (Blueprint $table) {
                $table->dropColumn('extra_data');
            });
            $this->info('extra_data column dropped!');
        } else {
            $this->line('extra_data column does not exist!');
        }
    }
}
