<?php

namespace App\Services\Claymore;

use App\Models\Character\CharacterClass;
use App\Models\Character\CharacterClassType;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class ClassTypeService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Class Type Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of class types.
    |
    */

    /**
     * Create a class type.
     *
     * @param array $data
     *
     * @return bool|CharacterClassType
     */
    public function createClassType($data) {
        DB::beginTransaction();

        try {
            $data = $this->populateClassData($data);

            $class = CharacterClassType::create($data);

            return $this->commitReturn($class);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a class.
     *
     * @param CharacterClassType $class
     * @param array              $data
     *
     * @return bool|CharacterClassType
     */
    public function updateClassType($class, $data) {
        DB::beginTransaction();

        try {
            if (CharacterClassType::where('name', $data['name'])->where('id', '!=', $class->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateClassData($data, $class);

            $class->update($data);

            return $this->commitReturn($class);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Delete a class.
     *
     * @param CharacterClassType $class
     *
     * @return bool
     */
    public function deleteClassType($class) {
        DB::beginTransaction();

        try {
            // Check first if the class is currently in use
            if (CharacterClass::where('id', $class->id)->exists()) {
                throw new \Exception('A class with this type currently exists. Please change its type before deleting this type.');
            }

            $class->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handle class data.
     *
     * @param array                   $data
     * @param CharacterClassType|null $class
     *
     * @return array
     */
    private function populateClassData($data, $class = null) {
        if (isset($data['description']) && $data['description']) {
            $data['description'] = parse($data['description']);
        }

        return $data;
    }
}
