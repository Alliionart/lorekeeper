<?php

namespace App\Services;

use App\Models\Breeding\Breeding;
use Illuminate\Support\Facades\DB;

class BreedingService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Breeding Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of breeding requests.
    |
    */

    /**
     * Creates a new breeding.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Breeding\Breeding|bool
     */
    public function createBreeding($data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $breeding = Breeding::create($data);

            if (!$this->logAdminAction($user, 'Created Breeding Request', 'Created '.$breeding->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($breeding);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a breeding.
     *
     * @param \App\Models\Breeding\Breeding $breeding
     * @param array                         $data
     * @param \App\Models\User\User         $user
     *
     * @return \App\Models\Breeding\Breeding|bool
     */
    public function updateBreeding($breeding, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Breeding::where('id', '!=', $breeding->id)->exists()) {
                throw new \Exception('Breeding with this ID already exists.');
            }

            $data = $this->populateData($data);

            $breeding->update($data);

            if (!$this->logAdminAction($user, 'Updated Breeding Request', 'Updated '.$breeding->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($breeding);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a breeding.
     *
     * @param \App\Models\Breeding\Breeding $breeding
     * @param mixed                         $user
     *
     * @return bool
     */
    public function deleteBreeding($breeding, $user) {
        DB::beginTransaction();

        try {
            if (!$this->logAdminAction($user, 'Deleted Breeding Request', 'Deleted '.$breeding->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            $breeding->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update the breeding settings.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function updateBreedingSettings($data, $user) {
        DB::beginTransaction();

        try {
            \Log::info($data);

            $litter_config = [];
            $species_rates = [];
            $subtype_rates = [];
            $trait_rates = [];
            $marking_rates = [];
            $mutation_rates = [];
            $modifiers = [];

            $db_keys = [
                'marking_rates' => DB::table('site_settings')->where('key', 'marking_rates'),
            ];

            //Loop through all of the data and use a switch/case to move it into the correct setting
            foreach ($data as $key => $value) {
                switch (true) {
                    case str_contains($key, 'litter_size'):
                        $full_id = str_replace('litter_size_', '', $key);
                        $range = explode('_', $full_id)[0]; // min or max
                        $species_id = explode('_', $full_id)[1];
                        $litter_config[$species_id] = [
                            'min'   => $value ?? 1,
                            'max'   => $value ?? 5,
                        ];
                        break;
                    case str_contains($key, 'species_id'):
                        //Stuff
                        break;
                    case str_contains($key, 'subtype'):
                        //Stuff
                        break;
                    case str_contains($key, 'trait_rarity'):
                        //Stuff
                        break;
                    case str_contains($key, 'marking_rate'):
                        if ($value !== null) {
                            if (str_contains($key, 'marking_rate_dom')) {
                                //Dom Rate
                                $full_id = str_replace('marking_rate_dom_', '', $key);
                                $name = 'roll_dom';
                            } else {
                                //Regular Rate
                                $full_id = str_replace('marking_rate_', '', $key);
                                $name = 'rate';
                            }
                            $rarity_name = ucwords(explode('__', $full_id)[0]); //Common
                            $pairing = explode('__', $full_id)[1];
                            $marking_rates[$rarity_name][$pairing][$name] = $value;
                        }
                        break;
                    case str_contains($key, 'mutation_'):
                        //Stuff
                        break;
                    case str_contains($key, 'mod_'):
                        //Stuff
                        break;
                }
            }

            if ($litter_config) {
                $exists = DB::table('site_settings')->where('key', 'litter_config')->first();
                $litter_config = json_encode($litter_config);
                if ($exists) {
                    //Update
                    if ($exists->value !== $litter_config) {
                        DB::table('site_settings')->where('key', 'litter_config')->update(['value' => $litter_config]);
                    }
                } else {
                    //Create
                    DB::table('site_settings')->insert(['key' => 'litter_config', 'value' => $litter_config, 'description' => 'Auto-Generated']);
                }
            }
            if ($marking_rates) {
                $exists = DB::table('site_settings')->where('key', 'marking_rates')->first();
                $marking_rates = json_encode($marking_rates);
                if ($exists) {
                    //Update
                    if ($exists->value !== $marking_rates) {
                        DB::table('site_settings')->where('key', 'marking_rates')->update(['value' => $marking_rates]);
                    }
                } else {
                    //Create
                    DB::table('site_settings')->insert(['key' => 'marking_rates', 'value' => $marking_rates, 'description' => 'Auto-Generated']);
                }
            }

            if (!$this->logAdminAction($user, 'Updated Breeding Settings', 'Updated breeding settings')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a base.
     *
     * @param array                         $data
     * @param \App\Models\Breeding\Breeding $breeding
     *
     * @return array
     */
    private function populateData($data, $breeding = null) {
        //Set up the character_data column
        if (!isset($data['parent_1_id']) || $data['parent_1_id'] == 0) {
            //Parent 1
            //Parent 2
        }
        //Set up breeding data with modifiers and images

        return $data;
    }
}
