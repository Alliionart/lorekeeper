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
