<?php

namespace App\Services\Claymore;

use App\Models\Claymore\Ability;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class AbilityService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Character Ability Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of character categories.
    |
    */

    /**
     * Create a ability.
     *
     * @param array $data
     *
     * @return Ability|bool
     */
    public function createAbility($data) {
        DB::beginTransaction();

        try {
            $data = $this->populateAbilityData($data);

            $ability = Ability::create($data);

            return $this->commitReturn($ability);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a ability.
     *
     * @param Ability $ability
     * @param array   $data
     *
     * @return Ability|bool
     */
    public function updateAbility($ability, $data) {
        DB::beginTransaction();

        try {
            if (Ability::where('name', $data['name'])->where('id', '!=', $ability->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateAbilityData($data, $ability);

            $ability->update($data);

            return $this->commitReturn($ability);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Delete a ability.
     *
     * @param Ability $ability
     *
     * @return bool
     */
    public function deleteAbility($ability) {
        DB::beginTransaction();

        try {
            // Check first if the ability is currently in use
            if (CharacterClass::where('ability_id', $ability->id)->exists()) {
                throw new \Exception('A class with this ability exists. Please change its ability first.');
            }

            $ability->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handle ability data.
     *
     * @param array        $data
     * @param Ability|null $ability
     *
     * @return array
     */
    private function populateAbilityData($data, $ability = null) {
        unset($data['name']);
        unset($data['type_id']);

        if (isset($data['description']) && $data['description']) {
            $description = parse($data['description']);
        }

        $ability_data = [
            'cooldown'      => $data['cooldown'] ?? 0,
            'free_action'   => $data['free_action'] ?? 0,
            'chance'        => $data['chance'] ?? 0,
            'success'       => [],
            'failure'       => []
        ];

        /**
         * cooldown
         * free_action
         * chance
         * success
         *      -
         * failure
         *      - 
         */

        unset($data['cooldown']);
        unset($data['free_action']);
        unset($data['chance']);

        foreach($data as $key => $value) {
            if ($value) {
                switch (true) {
                    case (str_contains($key, 'success')):
                        //Is part of the success key
                        $key = str_replace('success_', '', $key);
                        if(!str_contains($key, '__')) {
                            $ability_data['success'][$key] = $value;
                        } else {
                            $i = explode('__', $key)[0];
                            $nKey = explode('__', $key)[1];
                            $ability_data['success']['effects'][$i][$nKey] = $value;
                        }
                        break;
                    case (str_contains($key, 'failure')):
                        //Is part of the failure key
                        $key = str_replace('failure_', '', $key);
                        if(!str_contains($key, '__')) {
                            $ability_data['failure'][$key] = $value;
                        } else {
                            $i = explode('__', $key)[0];
                            $nKey = explode('__', $key)[1];
                            $ability_data['failure']['effects'][$i][$nKey] = $value;
                        }
                        break;
                }
            }
        }
        \Log::info($ability_data);
        

        return $data;
    }
}
