<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakePluginMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:make-migration {plugin : The folder name of the plugin} {name : The name of the migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new migration file to the specified plugin.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $plugin = $this->argument('plugin');
        $name = $this->argument('name');

        $path = "plugins/{$plugin}/database/migrations";

        $this->call('make:migration', [
            'name'      => $name,
            '--path'    => $path,
        ]);
    }
}
