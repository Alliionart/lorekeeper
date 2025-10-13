<?php

namespace App\Services\Item;

use App\Models\Character\Character;
use App\Models\Pet\Pet;
use App\Models\Pet\PetCategory;
use App\Models\User\UserItem;
use App\Services\InventoryManager;
use App\Services\Service;
use DB;

class TrapService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Trap Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of trap type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        return [
            'pet_categories' => PetCategory::orderBy('name', 'DESC')->pluck('name', 'id'),
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
        $trapData = [];
        $trapData['pet_category'] = $tag->data['pet_category'] ?? null;
        $trapData['chance'] = $tag->data['chance'] ?? null;

        return $trapData;
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

            $trapData['pet_category'] = $data['pet_category'];
            $trapData['chance'] = $data['chance'];
            $tag->update(['data' => json_encode($trapData)]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param \App\Models\User\UserItem $stacks
     * @param \App\Models\User\User     $user
     * @param array                     $data
     *
     * @return bool
     */
    public function act($stacks, $user, $data) {
        DB::beginTransaction();

        try {
            // foreach ($stacks as $key=>$stack) {
            //     // We don't want to let anyone who isn't the owner of the box open it,
            //     // so do some validation...
            //     if ($stack->user_id != $user->id) {
            //         throw new \Exception('This item does not belong to you.');
            //     }

            //     $character = Character::where('id', $data['trap_character_id'])->first();
            //     if (!$character) {
            //         throw new \Exception('Invalid character selected.');
            //     }

            //     // Next, try to delete the item. If successful, we can start distributing rewards.
            //     if ((new InventoryManager)->debitStack($stack->user, 'Trap Applied', ['data' => ''], $stack, $data['quantities'][$key])) {
            //         for ($q = 0; $q < $data['quantities'][$key]; $q++) {
            //             // Distribute user rewards
            //             if (!$rewards = fillCharacterAssets(parseAssetData($stack->item->tag('trap')->data), $user, $character, 'Trap Applied', [
            //                 'data' => 'Trap status by using '.$stack->item->name,
            //             ])) {
            //                 throw new \Exception('Failed to use trap.');
            //             }
            //         }
            //     }
            // }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
