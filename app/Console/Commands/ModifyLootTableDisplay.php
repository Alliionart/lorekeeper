<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App;
use Carbon\Carbon;
use App\Models\Loot\LootTable;

class ModifyLootTableDisplay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modify-loot-table-display';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates *ALL* loot table "disclose_loots" values.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('************************************************');
        $this->info('* MODIFY LOOT TABLES "DISCLOSE_LOOTS" VALUES *');
        $this->info('************************************************'."\n");

        $this->line('This will change *ALL* disclose_loots values.');

        if($this->confirm('Old data will not be retrievable. Do you wish to continue?')) {
                $selection = $this->choice('What value do you wish to set all loot tables to?', ['0: Table loots are hidden.', '1: Users can see both loots and drop rates.', '2: Users can see loots, but not drop rates.', '3: Cancel']);
                $newDiscloseLoots = $selection[0];

                if ($newDiscloseLoots != 3) {
                    $this->line('Updating all loot tables disclose_loots to '.$newDiscloseLoots.'...'."\n");

                    $tables = LootTable::query()->update(['disclose_loots' => $newDiscloseLoots]);

                    $this->info('All values updated successfully!');
                    $this->line('Please visit the admin dashboard to change visibility on specific tables.');
                } else {
                    $this->error('You\'ve selected to cancel any updates.');
                    $this->line('No changes have been made.');
                }

        }

    }
}