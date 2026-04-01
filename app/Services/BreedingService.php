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
     * @return bool|Breeding
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
     * @param Breeding              $breeding
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Breeding
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
     * @param Breeding $breeding
     * @param mixed    $user
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

        //dd($data);

        try {
            $litter_config = [];
            $species_rates = [];
            $subtype_rates = [];
            $trait_rates = [];
            $marking_rates = [];
            $mutation_rates = [];
            $modifiers = [];
            $skills_rates = [];
            $inbreeding_rates = [];

            if($data['species_rates']) {
                //Refactor the array BEFORE saving
                foreach ($data['species_rates'] as $pairing => $row) {
                    $species_1 = isset($row['species_id'][0]) ? $row['species_id'][0] : null;
                    $species_2 = isset($row['species_id'][1]) ? $row['species_id'][1] : null;

                    if (isset($species_1) && isset($species_2)) {
                        $species_name_0 = Species::where('id', $species_1)->pluck('name');
                        $species_name_1 = Species::where('id', $species_2)->pluck('name');

                        $species_name_0 = isset($species_name_0[0]) ? $species_name_0[0] : null;
                        $species_name_1 = isset($species_name_1[0]) ? $species_name_1[0] : null;

                        if ($species_name_0 && $species_name_1) {
                            $count = count($row['results']);

                            $species_rates[$species_name_0.'|'.$species_name_1] = [
                                $row['species_id'][0] => $row['results'][0] ?? 100 / $count,
                                $row['species_id'][1] => $row['results'][1] ?? 100 / $count,
                            ];
                        }
                    }
                }
                if ($species_rates && count($species_rates) > 0) {
                    //Save the info in the DB
                    $this->saveBreedingSetting('breeding_species_rates', $species_rates);
                }
            }

            if($data['litter_size']) {
                //Check the array for null values and throw an error if any are found
                $litter_config = [];
                foreach($data['litter_size'] as $species_id => $row) {
                    $min = $row['min'] ?? 1;
                    $max = $row['max'] ?? 5;
                    if ($species_id && $min && $max) {
                        $litter_config[$species_id] = $row;
                    }
                }
                if ($litter_config && count($litter_config) > 0) {
                    //Save the info in the DB
                    $this->saveBreedingSetting('breeding_litter_config', $litter_config);
                }
            }

            if($data['marking_rate']) {
                //Check the array for null values and throw an error if any are found
                $marking_rates = [];
                foreach($data['marking_rate'] as $rarity => $row) {
                    foreach($row as $type => $fields) {
                        $fields['roll_dom'] = $fields['roll_dom'] ?? 0;
                        if (!$fields['rate']) {
                            unset($marking_rates[$rarity][$type]);
                        }
                    }
                }
                if ($marking_rates && count($marking_rates) > 0) {
                    //Save the info in the DB
                    $this->saveBreedingSetting('breeding_marking_rates', $marking_rates);
                }
            }

            if($data['subtypes']) {
                //Check the array for null values and throw an error if any are found
                $subtypes = [];
                $ni = 0;
                foreach($data['subtypes'] as $i => $row) {
                    foreach($row as $j => $val) {
                        if (!$val) {
                            throw new \Exception('There was an error with subtype rates at row '.$ni.'. Please ensure all subtypes have a species and rarity selected.');
                        }
                    }
                    $subtypes[$ni] = $row;
                    $ni++;
                }
                if ($subtypes && count($subtypes) > 0) {
                    //Save the info in the DB
                    $this->saveBreedingSetting('breeding_subtype_rates', $subtypes);
                }
            }

            if($data['traits']) {
                //Check the array for null values and throw an error if any are found
                $traits = [];
                $ni = 0;
                foreach($data['traits'] as $i => $row) {
                    foreach($row as $j => $val) {
                        if (!$val) {
                            throw new \Exception('There was an error with traits rates at row '.$ni.'. Please ensure all trait fields are filled out.');
                        }
                    }
                    $traits[$ni] = $row;
                    $ni++;
                }
                if ($traits && count($traits) > 0) {
                    //Save the info in the DB
                    $this->saveBreedingSetting('breeding_trait_rates', $traits);
                }
            }

            if ($data['mutations']) {
                //Refactor the array BEFORE saving
                foreach ($data['mutations'] as $i => $row) {
                    $trait_category_name = FeatureCategory::where('id', $row['category'])->pluck('name')[0];
                    if ( $row['rarity'] !== 0 || $row['rarity'] !== '0' ) {
                        $rarity_name = Rarity::where('id', $row['rarity'])->pluck('name');
                        $rarity_name = isset($rarity_name[0]) ? $rarity_name[0] : null;
                    } else {
                        $rarity_name = null;
                    }
                    $temp = $row;
                    $key = $trait_category_name . ($rarity_name ? '|'.$rarity_name : '');
                    $mutation_rates[$key] = $row;
                }
                if ($mutation_rates && count($mutation_rates) > 0) {
                    //Save the info in the DB
                    $this->saveBreedingSetting('breeding_mutation_rates', $mutation_rates);
                }
            }

            if($data['skill_rates']) {
                foreach($data['skill_rates'] as $rarity_id => $rate) {
                    if ($rate) {
                        $skills_rates[$rarity_id] = $rate;
                    }
                }

                if ($skills_rates && count($skills_rates) > 0) {
                    //Save the info in the DB
                    $this->saveBreedingSetting('breeding_skills_rates', $skills_rates);
                }
            }

            if($data['mod']) {
                foreach($data['mod'] as $id => $row) {
                    if (isset($row['type']) && isset($row['item']) && $row['type'] && $row['rate']) {
                        $modifiers['items'][$row['item']] = [
                            'type' => $row['type'],
                            'rate' => $row['rate'],
                        ];
                    }
                }
                if ($modifiers && count($modifiers) > 0) {
                    //Save the info in the DB
                    $this->saveBreedingSetting('breeding_modifiers', $modifiers);
                }
            }

            if($data['inbreeding_trait']) {
                foreach($data['inbreeding_trait'] as $trait_id => $rate) {
                    if ($rate) {
                        $inbreeding_rates[$trait_id] = $rate;
                    }
                }
                if ($inbreeding_rates && count($inbreeding_rates) > 0) {
                    //Save the info in the DB
                    $this->saveBreedingSetting('breeding_inbreeding_trait_rates', $inbreeding_rates);
                }
            }

            if (!$this->logAdminAction($user, 'Updated Breeding Settings', 'Updated breeding settings')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            dd($e);
            $this->setError('error', $e->getLine().': '.$e->getMessage());
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
