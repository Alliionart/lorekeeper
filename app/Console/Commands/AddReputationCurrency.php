<?php

namespace App\Console\Commands;

use App\Models\User\User;
use App\Services\CurrencyService;
use DB;
use Illuminate\Console\Command;
use Settings;

class AddReputationCurrency extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-reputation-currency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds the reputation currency able to be held by users, characters and guilds.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /** Execute the console command.
     * @return mixed
     */
    public function handle() {
        $this->info('====================  ADD REPUTATION ====================');
        $this->line('This command will create a new currency which tracks reputation by users, characters and guilds. It will add a site setting for the currency.\n');
        $this->line('This command should only be run once.');

        if ($this->confirm('Do you want to continue?')) {
            $this->line('By default all Users, Characters and Guilds can hold Reputation. This may be changed later in the currency settings.');
            $this->line('Adding reputation...');

            $data = [
                'is_user_owned'         => true,
                'is_character_owned'    => true,
                'is_guild_owned'        => true,
                'name'                  => 'Reputation',
                'abbreviation'          => 'Rep',
                'description'           => '<p>Reputation of the holder.</p>',
            ];

            $currency = (new CurrencyService)->createCurrency($data, User::find(Settings::get('admin_user')));
            $this->info('Added: Reputation');

            //Add to site settings
            $this->line("Adding site setting...\n");

            if (!DB::table('site_settings')->where('key', 'guild_reputation_currency')->exists()) {
                DB::table('site_settings')->insert([
                    [
                        'key'         => 'guild_reputation_currency',
                        'value'       => $currency->id,
                        'description' => 'The ID of the reputation currency.',
                    ],
                ]);
                $this->info('Added: guild_reputation_currency');
            } else {
                $this->info('Skipped: guild_reputation_currency');
            }

            $this->line('Done!');
        }
    }
}
