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
     * @return bool|Ability
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
     * @param array          $data
     *
     * @return bool|Ability
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
     * @param array               $data
     * @param Ability|null $ability
     *
     * @return array
     */
    private function populateAbilityData($data, $ability = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        return $data;
    }
}
