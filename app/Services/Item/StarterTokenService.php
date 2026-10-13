<?php

namespace App\Services\Item;

use App\Models\Character\Character;
use App\Models\Character\CharacterCategory;
use App\Services\Service;
use DB;

class StarterTokenService extends Service {
    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        $categories = CharacterCategory::whereIn('name', ['Starter', 'NPC'])->pluck('id')->toArray();

        return [
            'characters' => Character::whereIn('character_category_id', $categories)->where('is_myo_slot', 0)->get()->pluck('fullName', 'id'),
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param string $tag
     *
     * @return mixed
     */
    public function getTagData($tag) {
        return $tag->data ?? null;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param string $tag
     * @param array  $data
     *
     * @return bool
     */
    public function updateData($tag, $data) {
        DB::beginTransaction();

        try {
            $tag->update(['data' => json_encode($data['characters'])]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param \App\Models\User\UserItem $stack
     * @param \App\Models\User\User     $user
     * @param array                     $data
     *
     * @return bool
     */
    public function act($stack, $user, $data) {
        return true;
    }
}
