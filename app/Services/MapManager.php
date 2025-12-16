<?php

namespace App\Services;

use App\Models\Map;
use Illuminate\Support\Facades\DB;

class MapManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Map Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of site maps.
    |
    */

    /**
     * Creates a site map.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Map|bool
     */
    public function createMapItem($data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['text']) && $data['text']) {
                $data['parsed_text'] = parse($data['text']);
            }
            $data['user_id'] = $user->id;
            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }

            $map = Map::create($data);

            return $this->commitReturn($map);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a site map.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param mixed                 $map
     *
     * @return \App\Models\Map|bool
     */
    public function updateMapItem($map, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Map::where('key', $data['key'])->where('id', '!=', $map->id)->exists()) {
                throw new \Exception('The key has already been taken.');
            }

            if (isset($data['text']) && $data['text']) {
                $data['parsed_text'] = parse($data['text']);
            }
            $data['user_id'] = $user->id;
            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }

            $map->update($data);

            return $this->commitReturn($map);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a site map.
     *
     * @param mixed $map
     *
     * @return bool
     */
    public function deleteMapItem($map) {
        DB::beginTransaction();

        try {
            // Specific maps such as the TOS/privacy policy cannot be deleted from the admin panel.
            if (config('lorekeeper.text_maps.'.$map->key)) {
                throw new \Exception('You cannot delete this map.');
            }

            $map->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
