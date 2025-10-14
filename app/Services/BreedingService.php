<?php

namespace App\Services;

use App\Models\Breeding\Breeding;
use App\Models\Feature\FeatureCategory;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
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
            $inbreeding_rates = [];

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
                        $litter_config[$species_id][$range] = ($range === 'max' ? ($value ?? 5) : ($value ?? 1));
                        break;
                    case str_contains($key, 'species_id'):
                        foreach ($value as $i => $val) {
                            $species_rates[$i][$key] = $val;
                        }
                        break;
                    case str_contains($key, 'subtype'):
                        foreach ($value as $i => $val) {
                            $subtype_rates[$i][$key] = $val;
                        }
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
                        $field = str_replace('mutation_', '', $key);
                        foreach ($value as $i => $val) {
                            $mutation_rates[$i][$field] = $val;
                        }
                        break;
                    case str_contains($key, 'mod_'):
                        //Stuff
                        break;
                }
            }

            if ($species_rates) {
                //Refactor the array BEFORE saving
                foreach ($species_rates as $i => $row) {
                    $species_name_0 = Species::where('id', $row['species_id_0'])->pluck('name')[0];
                    $species_name_1 = Species::where('id', $row['species_id_1'])->pluck('name')[0];
                    $temp = $row;
                    unset($species_rates[$i]);
                    $species_rates[$species_name_0.'|'.$species_name_1] = $row;
                }
                //Save the info in the DB
                $this->saveBreedingSetting('species_rates', $species_rates);
            }

            // if($subtype_rates) {
            //     //Refactor the array BEFORE saving
            //     foreach($subtype_rates as $i => $row) {
            //         $subtype_name_0 = Subtype::where('id', $row['subtype_0'])->pluck('name')[0];
            //         $subtype_name_1 = Subtype::where('id', $row['subtype_1'])->pluck('name')[0];
            //         $temp = $row;
            //         unset($subtype_rates[$i]);
            //         $subtype_rates[$subtype_name_0 . '|' . $subtype_name_1] = $row;
            //     }
            //     //Save the info in the DB
            //     //$this->saveBreedingSetting('species_rates', $species_rates);
            // }
            //\Log::info($subtype_rates);

            

            if ($mutation_rates) {
                //Refactor the array BEFORE saving
                foreach ($mutation_rates as $i => $row) {
                    $trait_category_name = FeatureCategory::where('id', $row['category'])->pluck('name')[0];
                    $rarity_name = Rarity::where('id', $row['rarity'])->pluck('name')[0];
                    $temp = $row;
                    unset($mutation_rates[$i]);
                    $mutation_rates[$trait_category_name.'|'.$rarity_name] = $row;
                }
                $this->saveBreedingSetting('mutation_rates', $mutation_rates);
            }

            $this->saveBreedingSetting('litter_config', $litter_config);
            $this->saveBreedingSetting('marking_rates', $marking_rates);

            if (!$this->logAdminAction($user, 'Updated Breeding Settings', 'Updated breeding settings')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    public function saveBreedingSetting($key, $value) {
        if ($value) {
            $exists = DB::table('site_settings')->where('key', $key)->first();
            $value = json_encode($value);
            if ($exists) {
                //Update
                if ($exists->value !== $value) {
                    DB::table('site_settings')->where('key', $key)->update(['value' => $value]);
                }
            } else {
                //Create
                DB::table('site_settings')->insert(['key' => $key, 'value' => $value, 'description' => 'Auto-Generated']);
            }
        }
    }
}
