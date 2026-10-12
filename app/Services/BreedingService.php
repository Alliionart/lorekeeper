<?php

namespace App\Services;

use App\Models\Breeding\Breeding;
use App\Models\Character\Character;
use App\Models\Currency\Currency;
use App\Models\Feature\FeatureCategory;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\User\User;
use App\Models\User\UserItem;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Notifications;
use Settings;

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
            if (!$this->checkBreedingOpen()) {
                throw new \Exception('Breeding is currently closed!');
            }

            switch ($data['type']) {
                case 'Full':
                    if ($user->leftBreedSlots['full'] < 1) {
                        throw new \Exception('You are out of full slots for the month!');
                    }
                    break;
                case 'Split':
                    if ($user->leftBreedSlots['split'] < 1) {
                        throw new \Exception('You are out of split slots for the month!');
                    }
                    break;
            }

            $split = false;
            $data = $this->populateData($data);
            $item_ids = isset($data['items']) ? collect($data['items'])->pluck('item_id')->toArray() : [];
            $characterData = $data['characterData'];

            if (isset($data['items']) && $data['items'] && count($data['items'])) {
                $data['items'] = $this->fetchItemStacks($data['items'], $user);
            }
            if (isset($data['split_status'])) {
                $split = true;
                if (isset($data['split_user_id'])) {
                    $split_user = User::find($data['split_user_id']);
                } else {
                    // Check for greeds token
                    if ($this->hasGreeds($data['items'])) {
                        $data['split_status'] = 'Converted';
                    }
                }
            }

            $modifiers = json_decode(Settings::get('breeding_modifiers')) ?? null;
            $items = $modifiers ? (array) $modifiers->items : null;
            $item_id = collect($items)->search(function ($item) {
                return is_object($item) ? $item->type === 'sex_override' : $item['type'] === 'sex_override';
            });

            // Check genders
            $parent_1 = Character::find($characterData[1]['character_id']);
            $parent_2 = Character::find($characterData[2]['character_id']);

            if ($this->checkInbreeding((object) ['parent_1' => $parent_1, 'parent_2' => $parent_2])) {
                throw new \Exception('Inbreeding detected! You cannot breed these parents due to shared lineage.');
            }

            if (!$this->checkParentsBreedable($parent_1, $parent_2)) {
                throw new \Exception('One or both parents are not breedable!');
            }

            if ($parent_1->sex === $parent_2->sex && !in_array($item_id, $item_ids)) {
                throw new \Exception('Both parents are the same gender! You must provide an Essence Vessel to breed same gendered parents.');
            }

            $item_id = collect($items)->search(function ($item) {
                return is_object($item) ? $item->type === 'subtype_override' : $item['type'] === 'subtype_override';
            });

            // Check petities & species
            if ($parent_1->species_id !== $parent_2->species_id && !in_array($item_id, $item_ids)) {
                throw new \Exception('Parents are different species! You cannot use Physique Petities unless the parents are the same species.');
            }

            $breeding = Breeding::create([
                'user_id'           => $user->id,
                'status'            => 'Pending',
                'type'              => $data['type'] ?? 'Full',
                'parent_1'          => $characterData[1]['character_id'],
                'parent_2'          => $characterData[2]['character_id'],
                'permission_1'      => $characterData[1]['permission_id'] ?? null,
                'permission_2'      => $characterData[2]['permission_id'] ?? null,
                'character_data'    => json_encode($characterData),
                'breeding_data'     => isset($data['items']) ? json_encode($data['items']) : null,
                'split_user_id'     => $data['split_user_id'] ?? null,
                'split_status'      => $data['split_status'] ?? null,
            ]);

            if ($split) {
                Notifications::create('BREEDING_SPLIT_REQUEST', $split_user, [
                    'sender_name'       => $user->name,
                    'sender_url'        => $user->url,
                    'url'               => $breeding->url,
                    'id'                => $breeding->id,
                ]);
            }

            if (!$this->createLog($user->id, 'Created Breeding Request', 'Created', [], $breeding)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn($breeding);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Edits an existing breeding.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param mixed                 $breeding
     *
     * @return bool|Breeding
     */
    public function editBreeding($breeding, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$this->checkBreedingOpen()) {
                throw new \Exception('Breeding is currently closed!');
            }

            $previousCharacterData = $breeding->characterData ?? null;
            $previousItemData = $breeding->breedingData && isset($breeding->breedingData->user->user_items)
                ? (array) $breeding->breedingData->user->user_items
                : [];

            switch ($data['type'] !== $breeding->type) {
                case 'Full':
                    if ($user->leftBreedSlots['full'] < 1) {
                        throw new \Exception('You are out of full slots for the month!');
                    }
                    break;
                case 'Split':
                    if ($user->leftBreedSlots['split'] < 1) {
                        throw new \Exception('You are out of split slots for the month!');
                    }
                    break;
            }

            $split = false;
            $data = $this->populateData($data);
            $item_ids = isset($data['items']) ? collect($data['items'])->pluck('item_id')->toArray() : [];
            $characterData = $data['characterData'];

            if (isset($data['items']) && $data['items'] && count($data['items'])) {
                $data['items'] = $this->fetchItemStacks($data['items'], $user);
            }

            $newItemData = isset($data['items']['user']['user_items']) ? (array) $data['items']['user']['user_items'] : [];
            $characterChanged = $previousCharacterData !== null && json_encode($previousCharacterData) !== json_encode($characterData);
            $itemsChanged = json_encode($previousItemData) !== json_encode($newItemData);

            if ($characterChanged || $itemsChanged) {
                $this->syncEditedBreedingItemStacks($user, $previousItemData, $newItemData);
            }

            if (isset($data['split_status'])) {
                $split = true;
                if (isset($data['split_user_id'])) {
                    $split_user = User::find($data['split_user_id']);
                } else {
                    // Check for greeds token
                    if ($this->hasGreeds($data['items'])) {
                        $data['split_status'] = 'Converted';
                    }
                }
            }

            $modifiers = json_decode(Settings::get('breeding_modifiers')) ?? null;
            $items = $modifiers ? (array) $modifiers->items : null;
            $item_id = collect($items)->search(function ($item) {
                return is_object($item) ? $item->type === 'sex_override' : $item['type'] === 'sex_override';
            });

            // Check genders
            $parent_1 = Character::find($characterData[1]['character_id']);
            $parent_2 = Character::find($characterData[2]['character_id']);

            if (!$this->checkParentsBreedable($parent_1, $parent_2)) {
                throw new \Exception('One or both parents are not breedable!');
            }

            if ($parent_1->sex === $parent_2->sex && !in_array($item_id, $item_ids)) {
                throw new \Exception('Both parents are the same gender! You must provide an Essence Vessel to breed same gendered parents.');
            }

            $item_id = collect($items)->search(function ($item) {
                return is_object($item) ? $item->type === 'subtype_override' : $item['type'] === 'subtype_override';
            });

            // Check petities & species
            if ($parent_1->species_id !== $parent_2->species_id && !in_array($item_id, $item_ids)) {
                throw new \Exception('Parents are different species! You cannot use Physique Petities unless the parents are the same species.');
            }

            $breeding->update([
                'status'            => 'Pending',
                'type'              => $data['type'] ?? 'Full',
                'parent_1'          => $characterData[1]['character_id'],
                'parent_2'          => $characterData[2]['character_id'],
                'permission_1'      => $characterData[1]['permission_id'] ?? null,
                'permission_2'      => $characterData[2]['permission_id'] ?? null,
                'character_data'    => json_encode($characterData),
                'breeding_data'     => isset($data['items']) ? json_encode($data['items']) : null,
                'split_user_id'     => $data['split_user_id'] ?? null,
                'split_status'      => $data['split_status'] ?? null,
                'updated_at'        => Carbon::now(),
            ]);

            if ($split) {
                Notifications::create('BREEDING_SPLIT_REQUEST', $split_user, [
                    'sender_name'       => $user->name,
                    'sender_url'        => $user->url,
                    'url'               => $breeding->url,
                    'id'                => $breeding->id,
                ]);
            }

            if (!$this->createLog($user->id, 'Created Breeding Request', 'Created', [], $breeding)) {
                throw new \Exception('Failed to create log.');
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

            if (!$this->createLog($user->id, 'Updated Breeding Request', 'Updated', [], $breeding)) {
                throw new \Exception('Failed to create log.');
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
     * Creates a breeding log.
     *
     * @param int    $senderId
     * @param string $log
     * @param string $logType
     * @param array  $data
     * @param object $breeding
     *
     * @return float
     */
    public function createLog($senderId, $log, $logType, $data, $breeding) {
        return DB::table('breeding_log')->insert(
            [
                'breeding_id'       => $breeding->id,
                'sender_id'         => $senderId,
                'parent_1_id'       => $breeding->parent1->id,
                'parent_2_id'       => $breeding->parent2->id,
                'log'               => $log,
                'log_type'          => $logType,
                'data'              => json_encode($data),
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]
        );

        return true;
    }

    /**
     * Rolls the specific breeding.
     *
     * @param mixed $data
     * @param mixed $staff
     */
    public function rollBreeding($data, $staff) {
        DB::beginTransaction();

        try {
            if (!isset($data['breeding_id']) || !$data['breeding_id']) {
                throw new \Exception('Breeding not found!');
            }

            $breeding = Breeding::find($data['breeding_id']);

            if ($staff->id === $breeding->user_id || $staff->id === $breeding->split_user_id) {
                throw new \Exception('You cannot roll your own breedings!');
            }

            $reroll = isset($data['reroll']) && $data['reroll'] ?? false;
            $is_split = $breeding->type === 'Split';
            $results = null;

            $roller = new BreedingRollerService($breeding);
            if (isset($data['remove_stack']) && count($data['remove_stack'])) {
                $stacks = $roller->removeItems($data['remove_stack']);
                $this->removeItemsFromBreeding($breeding, $data['remove_stack']);
            }
            $results = $roller->roll();
            $count = $results && is_array($results) ? count($results) : false;

            if ($results) {
                $breeding->update([
                    'results'   => json_encode($results),
                    'rerolls'   => $breeding->rerolls !== null ? $breeding->rerolls++ : 0,
                    'status'    => 'Approved',
                    'staff_id'  => Auth()->id(),
                ]);

                $data = [
                    'count' => $count,
                ];

                $this->createLog($staff->id, 'Breeding '.($reroll ? 'Re-' : '').'Rolled', ($reroll ? 'Re-' : '').'Rolled', $data, $breeding);
                if (!$reroll) {
                    $this->markPermsUsed($breeding, $staff);
                    $this->markItemsUsed($breeding, $stacks ?? null);
                    $this->markBreedingCurrencyUsed($breeding);

                    Notifications::create('BREEDING_ROLLED', $breeding->user, [
                        'sender_name'   => Auth::user()->name,
                        'sender_url'    => Auth::user()->url,
                        'url'           => $breeding->url,
                        'id'            => $breeding->id,
                    ]);
                    if ($is_split && ($split_user = User::find($breeding->split_user_id))) {
                        Notifications::create('BREEDING_ROLLED', $split_user, [
                            'sender_name'   => Auth::user()->name,
                            'sender_url'    => Auth::user()->url,
                            'url'           => $breeding->url,
                            'id'            => $breeding->id,
                        ]);
                    }

                    if (!$this->logAdminAction($staff, 'Breeding Approved', 'Approved '.$breeding->displayName)) {
                        throw new \Exception('Failed to log admin action.');
                    }
                } else {
                    Notifications::create('BREEDING_REROLLED', $breeding->user, [
                        'sender_name'   => Auth::user()->name,
                        'sender_url'    => Auth::user()->url,
                        'url'           => $breeding->url,
                        'id'            => $breeding->id,
                    ]);
                    if ($is_split && ($split_user = User::find($breeding->split_user_id))) {
                        Notifications::create('BREEDING_REROLLED', $split_user, [
                            'sender_name'   => Auth::user()->name,
                            'sender_url'    => Auth::user()->url,
                            'url'           => $breeding->url,
                            'id'            => $breeding->id,
                        ]);
                    }
                }

                return $this->commitReturn(true);
            } elseif (is_array($results) && empty($results)) {
                // Litter came back empty
                $breeding->update([
                    'results'   => json_encode([]),
                    'status'    => 'Approved',
                    'staff_id'  => Auth()->id(),
                ]);

                return $this->commitReturn(true);
            }

            throw new \Exception('Breeding roll produced no results.');
        } catch (\Exception $e) {
            $this->setError('error', $e->getLine().': '.$e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    public function cancelBreeding($breeding, $user) {
        DB::beginTransaction();

        try {
            if ($breeding->user_id != $user->id || !$user->isStaff) {
                throw new \Exception('Only the creator of this breeding may cancel it.');
            }

            $breeding->update([
                'status'  => 'Draft',
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getLine().': '.$e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approves the split breeding.
     *
     * @param App\Model\Breeding\Breeding $breeding
     * @param mixed                       $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveSplit($breeding, $user) {
        DB::beginTransaction();

        try {
            if ($breeding->split_user_id !== $user->id) {
                throw new \Exception('Only the split partner may approve this breeding.');
            }

            $breeding->update([
                'split_status'  => 'Approved',
            ]);

            Notifications::create('BREEDING_SPLIT_APPROVAL', $breeding->user, [
                'sender_name'   => $user->name,
                'sender_url'    => $user->url,
                'url'           => $breeding->url,
                'id'            => $breeding->id,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getLine().': '.$e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Rejects the split breeding.
     *
     * @param App\Model\Breeding\Breeding $breeding
     * @param mixed                       $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejectSplit($breeding, $user) {
        DB::beginTransaction();

        try {
            if ($breeding->split_user_id !== $user->id) {
                throw new \Exception('Only the split partner may reject this breeding.');
            }

            $split = User::find($user->id);

            $breeding->update([
                'split_status'  => 'Rejected',
            ]);

            Notifications::create('BREEDING_SPLIT_REJECTION', $breeding->user, [
                'sender_name'   => $split->name,
                'sender_url'    => $split->url,
                'url'           => $breeding->url,
                'id'            => $breeding->id,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getLine().': '.$e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Confirms the split breeding.
     *
     * @param App\Model\Breeding\Breeding $breeding
     * @param mixed                       $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirmSplit($breeding, $user) {
        DB::beginTransaction();

        try {
            if ($breeding->split_user_id !== $user->id) {
                throw new \Exception('Only the split partner may confirm this breeding.');
            }

            $breeding->update([
                'split_status'  => 'Confirmed',
            ]);

            Notifications::create('BREEDING_SPLIT_CONFIRMATION', $breeding->user, [
                'sender_name'   => $user->name,
                'sender_url'    => $user->url,
                'url'           => $breeding->url,
                'id'            => $breeding->id,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getLine().': '.$e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Marks the linked breeding permissions as used.
     *
     * @param App\Model\Breeding\Breeding $breeding
     * @param mixed                       $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markPermsUsed($breeding, $user) {
        $perm_1 = $breeding->permission1;
        $perm_2 = $breeding->permission2;
        $service = new CharacterManager();

        if ($perm_1) {
            $service->useBreedingPermission($breeding->parent1, $perm_1, $user, $breeding);
        }
        if ($perm_2) {
            $service->useBreedingPermission($breeding->parent2, $perm_2, $user, $breeding);
        }
    }

    public function markItemsUsed($breeding, $stacks = null) {
        if (!$stacks) {
            $stacks = $breeding->userItems;
        }

        if ($stacks && count($stacks)) {
            $service = new InventoryManager;
            foreach ($stacks as $row) {
                $stack = $row->userItem;
                $quantity = (int) ($row->quantity ?? 1);
                $stack->breeding_count = max(0, $stack->breeding_count - $quantity);
                $stack->save();

                $service->debitStack($breeding->user, 'Breeding Item Used', ['data' => 'Item used in Breeding #'.$breeding->id], $stack, $quantity);
            }
        }
    }

    /** ----------------------------------------------------
     *  PUP ACTIONS
     * ----------------------------------------------------.
     *
     * @param mixed $breeding
     * @param mixed $user
     * @param mixed $pupId
     * @param mixed $new_owner
     * @param mixed $exchange
     */

    /**
     * Converts the pup to a genotype and transfers to the applicable user.
     *
     * @param \App\Models\Breeding\Breeding $breeding
     * @param \App\Models\User\User         $user
     * @param int                           $pupId
     * @param bool|int                      $new_owner
     * @param string                        $exchange
     */
    public function convertPup($breeding, $user, $pupId, $new_owner, $exchange) {
        try {
            $pupId = (int) $pupId;
            $rawPup = $breeding->getLitterResults($pupId);
            $surrender = false;

            if (!$rawPup) {
                throw new \Exception('Pup not found.');
            }
            if (isset($rawPup->status) && $rawPup->status) {
                throw new \Exception('Pup was already processed!');
            }

            $pup = $breeding->getPupData($rawPup);

            if (!$pup) {
                throw new \Exception('Pup could not be generated - an error occured. Please contact an admin.');
            }

            $adopts_user = Settings::get('adopts_user');
            $rawPup->status = ($new_owner == $adopts_user || $new_owner === false) ? 'Surrendered' : 'Converted';
            $new_owner = $new_owner == false ? $adopts_user : $new_owner;

            if ($rawPup->status === 'Surrendered') {
                $releaseData = $breeding->getPupValue($pup, $exchange);
                $surrender = true;

                $markingCount = 0;
                foreach ($pup->raw_markings ?? [] as $group) {
                    $markingCount += count($group);
                }

                // Ensure the pup has more than 4 markings before surrendering
                if (!$pup->is_chimera && $markingCount < 4) {
                    throw new \Exception('Pup must have 4 or more markings to be surrendered.');
                }
            }

            // Format the data to be in the same format as the masterlist > create a MYO
            $data = [
                'user_id'                       => $new_owner ?? $adopts_user,
                'owner_url'                     => null,
                'name'                          => 'Breeding '.($surrender ? 'Surrender' : 'Request').' #'.$breeding->id.' - '.$breeding->parent1->name.' x '.$breeding->parent2->name.' - #'.($pupId + 1),
                'skills'                        => isset($pup->skills) ? $pup->skills->pluck('id')->toArray() : [],
                'description'                   => 'Converted from: '.$breeding->displayName,
                'is_visible'                    => 1,
                'sale_value'                    => null,
                'transferrable_at'              => null,
                'use_cropper'                   => 1,
                'x0'                            => 1,
                'x1'                            => 1,
                'y0'                            => 1,
                'y1'                            => 1,
                'designer_id'                   => [0 => null, 1 => null],
                'designer_url'                  => [0 => null, 1 => null],
                'artist_id'                     => [0 => null, 1 => null],
                'artist_url'                    => [0 => null, 1 => null],
                'species_id'                    => $pup->species->id,
                'subtype_id'                    => $pup->subtype->id,
                'rarity_id'                     => 1,
                'feature_id'                    => [],
                'feature_data'                  => [],
                'marking_id'                    => [],
                'is_dominant'                   => [],
                'base'                          => $rawPup->base,
                'secondary_base'                => null,
                'side_id'                       => [],
                'marking_color_0'               => [],
                'marking_color_1'               => [],
                'sex'                           => 'Genderless',
                'transformation_id'             => 0,
                'transformation_info'           => null,
                'transformation_description'    => null,
                'active_carriers'               => [],
                'sire_id'                       => $breeding->parent1->id,
                'dam_id'                        => $breeding->parent2->id,
                'generate_ancestors'            => 'generate',
                'stats'                         => [
                    1 => 1,
                    2 => 1,
                    3 => 1,
                    4 => 1,
                    5 => 1,
                ],
                'proof'             => [
                    'data'         => $breeding->displayName.' from '.$breeding->formattedCreatedAt,
                    'notes'        => null,
                ],
                'is_tradeable'      => true,
                'is_giftable'       => true,
                'active_carriers'   => isset($pup->carriers) && count($pup->carriers) ? $pup->carriers->pluck('id')->toArray() : [],
            ];

            //Format the features/traits
            $data['feature_id'][] = $pup->traits->ears->id;
            $data['feature_id'][] = $pup->traits->eyes->id;
            $data['feature_id'][] = $pup->traits->tail->id;
            $data['feature_id'][] = $pup->traits->fur->id;
            $data['feature_data'] = [
                0 => null,
                1 => null,
                2 => null,
                3 => null,
            ];
            if (isset($pup->aspect)) {
                $data['feature_id'][] = $pup->aspect->id;
                $data['feature_data'][] = null;
            }
            if (isset($pup->mutation)) {
                $data['feature_id'][] = $pup->mutation->id;
                $data['feature_data'][] = null;
            }
            if (isset($pup->inbred)) {
                $data['feature_id'][] = $pup->inbred->id;
                $data['feature_data'][] = null;
            }
            if ($pup->is_chimera) {
                $data['secondary_base'] = $pup->base[1]->id;
                $data['base'] = $pup->base[0]->id;
            }

            //Format the markings
            if ($pup->raw_markings && count($pup->raw_markings)) {
                if ($pup->is_chimera) {
                    $side_1 = $this->formatMarkingsForCreation($pup->raw_markings[0], 0) ?? [
                        'marking_id'        => [],
                        'is_dominant'       => [],
                        'side_id'           => [],
                        'marking_color_0'   => [],
                        'marking_color_1'   => [],
                    ];
                    $side_2 = $this->formatMarkingsForCreation($pup->raw_markings[1], 1) ?? [
                        'marking_id'        => [],
                        'is_dominant'       => [],
                        'side_id'           => [],
                        'marking_color_0'   => [],
                        'marking_color_1'   => [],
                    ];
                    $data['marking_id'] = array_merge($side_1['marking_id'], $side_2['marking_id']);
                    $data['is_dominant'] = array_merge($side_1['is_dominant'], $side_2['is_dominant']);
                    $data['side_id'] = array_merge($side_1['side_id'], $side_2['side_id']);
                    $data['marking_color_0'] = array_merge($side_1['marking_color_0'], $side_2['marking_color_0']);
                    $data['marking_color_1'] = array_merge($side_1['marking_color_1'], $side_2['marking_color_1']);
                } else {
                    $markingData = $this->formatMarkingsForCreation($pup->raw_markings);
                    $data['marking_id'] = $markingData['marking_id'];
                    $data['is_dominant'] = $markingData['is_dominant'];
                    $data['side_id'] = $markingData['side_id'];
                    $data['marking_color_0'] = $markingData['marking_color_0'];
                    $data['marking_color_1'] = $markingData['marking_color_1'];
                }
            }

            $service = new CharacterManager();

            // FINISH OFF
            if ($character = $service->createCharacter($data, Auth::user(), true)) {
                // Before redirecting update the pup JSON with status
                $breeding->updatePup($pupId, $rawPup);

                if ($rawPup->status === 'Surrendered') {
                    if (!isset($releaseData)) {
                        $releaseData = $breeding->getPupValue($pup, $exchange);
                    }

                    $service = new CurrencyManager();
                    $sender = $breeding->user;
                    $credited = $service->creditCurrency(Auth::user(), Auth::user(), 'User', 'Surrendered pup from '.$breeding->displayName.' to Adoption Center', $releaseData->currency, $releaseData->quantity);
                }

                return $this->commitReturn($character);
            } else {
                dd($service->errors()->all());
            }

            if ($service->hasErrors()) {
                foreach ($service->errors()->all() as $error) {
                    $this->setError('error', $error);
                }
            } else {
                $this->setError('error', 'Character creation failed without an explicit error.');
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getLine().': '.$e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * "Releases" the pup and provides the owner with currency credits.
     *
     * @param \App\Models\Breeding\Breeding $breeding
     * @param \App\Models\User\User         $user
     * @param int                           $pupId
     */
    public function releasePup($breeding, $user, $pupId) {
        DB::beginTransaction();

        try {
            $pupId = (int) $pupId;
            $rawPup = $breeding->getLitterResults($pupId);

            if (!$rawPup) {
                throw new \Exception('Pup not found.');
            }
            if (isset($rawPup->status) && $rawPup->status) {
                throw new \Exception('Pup was already processed!');
            }

            $pup = $breeding->getPupData($rawPup);

            if (!$pup) {
                throw new \Exception('Pup could not be released - an error occured. Please contact an admin.');
            }

            $rawPup->status = 'Released';

            // Finally start converting
            $releaseData = $breeding->getPupValue($pup, 'flat_krones');

            if ($releaseData && isset($releaseData->currency) && isset($releaseData->quantity)) {
                $service = new CurrencyManager();
                $sender = $breeding->user;
                $credited = $service->creditCurrency(Auth::user(), Auth::user(), 'User', 'Released pup from '.$breeding->displayName.' to the wild.', $releaseData->currency, $releaseData->quantity);
                if ($credited) {
                    $breeding->updatePup($pupId, $rawPup);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getLine().': '.$e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /** ----------------------------------------------------
     *  ADMIN SETTINGS FUNCTIONS
     * ----------------------------------------------------.
     *
     * @param mixed $data
     * @param mixed $user
     */

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
            $litter_config = [];
            $species_rates = [];
            $subtype_rates = [];
            $trait_rates = [];
            $marking_rates = [];
            $mutation_rates = [];
            $modifiers = [];
            $skills_rates = [];
            $inbreeding_rates = [];

            if ($data['species_rates']) {
                // Refactor the array BEFORE saving
                foreach ($data['species_rates'] as $pairing => $row) {
                    $species_1 = $row['species_id'][0] ?? null;
                    $species_2 = $row['species_id'][1] ?? null;

                    if (isset($species_1) && isset($species_2)) {
                        $species_name_0 = Species::where('id', $species_1)->pluck('name');
                        $species_name_1 = Species::where('id', $species_2)->pluck('name');

                        $species_name_0 = $species_name_0[0] ?? null;
                        $species_name_1 = $species_name_1[0] ?? null;

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
                    $this->saveBreedingSetting('breeding_species_rates', $species_rates);
                }
            }

            if ($data['litter_size']) {
                // Check the array for null values and throw an error if any are found
                $litter_config = [];
                foreach ($data['litter_size'] as $species_id => $row) {
                    $min = $row['min'] ?? 1;
                    $max = $row['max'] ?? 5;
                    if ($species_id && $min && $max) {
                        $litter_config[$species_id] = $row;
                    }
                }
                if ($litter_config && count($litter_config) > 0) {
                    $this->saveBreedingSetting('breeding_litter_config', $litter_config);
                }
            }

            if ($data['marking_rate']) {
                // Check the array for null values and throw an error if any are found
                $marking_rates = [];
                foreach ($data['marking_rate'] as $rarity => $row) {
                    foreach ($row as $type => $fields) {
                        $fields['roll_dom'] ??= 0;
                        if (!$fields['rate']) {
                            unset($marking_rates[$rarity][$type]);
                        }
                    }
                }
                if ($marking_rates && count($marking_rates) > 0) {
                    $this->saveBreedingSetting('breeding_marking_rates', $marking_rates);
                }
            }

            if ($data['subtypes']) {
                // Check the array for null values and throw an error if any are found
                $subtypes = [];
                $ni = 0;
                foreach ($data['subtypes'] as $i => $row) {
                    foreach ($row as $j => $val) {
                        if (!$val) {
                            throw new \Exception('There was an error with subtype rates at row '.$ni.'. Please ensure all subtypes have a species and rarity selected.');
                        }
                    }
                    $subtypes[$ni] = $row;
                    $ni++;
                }
                if ($subtypes && count($subtypes) > 0) {
                    $this->saveBreedingSetting('breeding_subtype_rates', $subtypes);
                }
            }

            if ($data['traits']) {
                // Check the array for null values and throw an error if any are found
                $traits = [];
                $ni = 0;
                foreach ($data['traits'] as $i => $row) {
                    foreach ($row as $j => $val) {
                        if (!$val) {
                            throw new \Exception('There was an error with traits rates at row '.$ni.'. Please ensure all trait fields are filled out.');
                        }
                    }
                    $traits[$ni] = $row;
                    $ni++;
                }
                if ($traits && count($traits) > 0) {
                    $this->saveBreedingSetting('breeding_trait_rates', $traits);
                }
            }

            if ($data['mutations']) {
                // Refactor the array BEFORE saving
                foreach ($data['mutations'] as $i => $row) {
                    $trait_category_name = FeatureCategory::where('id', $row['category'])->pluck('name')[0];
                    if ($row['rarity'] !== 0 || $row['rarity'] !== '0') {
                        $rarity_name = Rarity::where('id', $row['rarity'])->pluck('name');
                        $rarity_name = $rarity_name[0] ?? null;
                    } else {
                        $rarity_name = null;
                    }
                    $temp = $row;
                    $key = $trait_category_name.($rarity_name ? '|'.$rarity_name : '');
                    $mutation_rates[$key] = $row;
                }
                if ($mutation_rates && count($mutation_rates) > 0) {
                    $this->saveBreedingSetting('breeding_mutation_rates', $mutation_rates);
                }
            }

            if ($data['skill_rates']) {
                foreach ($data['skill_rates'] as $rarity_id => $rate) {
                    if ($rate) {
                        $skills_rates[$rarity_id] = $rate;
                    }
                }

                if ($skills_rates && count($skills_rates) > 0) {
                    // Save the info in the DB
                    $this->saveBreedingSetting('breeding_skills_rates', $skills_rates);
                }
            }

            if ($data['mod']) {
                foreach ($data['mod'] as $id => $row) {
                    if (isset($row['type']) && isset($row['item']) && $row['type']) {
                        $modifiers['items'][$row['item']] = [
                            'type' => $row['type'],
                            ...(isset($row['type']) && $row['type'] !== null ? ['rate' => $row['rate']] : []),
                        ];
                    }
                }
                if ($modifiers && count($modifiers) > 0) {
                    // Save the info in the DB
                    $this->saveBreedingSetting('breeding_modifiers', $modifiers);
                }
            }

            if ($data['inbreeding_trait']) {
                foreach ($data['inbreeding_trait'] as $trait_id => $rate) {
                    if ($rate) {
                        $inbreeding_rates[$trait_id] = $rate;
                    }
                }
                if ($inbreeding_rates && count($inbreeding_rates) > 0) {
                    // Save the info in the DB
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
                // Update
                if ($exists->value !== $value) {
                    DB::table('site_settings')->where('key', $key)->update(['value' => $value]);
                }
            } else {
                // Create
                DB::table('site_settings')->insert(['key' => $key, 'value' => $value, 'description' => 'Auto-Generated']);
            }
        }
    }

    /**
     * Cleans the data from the breeding form.
     *
     * @param array $data
     *
     * @return array
     */
    private function populateData($data) {
        $newData = [];
        $tokens = [];

        foreach ($data['parents'] as $i => $parent) {
            $newData['characterData'][$i]['character_id'] = (int) $parent['character_id'];

            switch ($parent['type']) {
                case 'permission':
                    $newData['characterData'][$i]['permission_id'] = (int) $parent['permission'];
                    $newData['characterData'][$i]['type'] = 'permission';
                    break;
                case 'starter':
                    $tokens[] = (int) $parent['starter']['token'];
                    $newData['characterData'][$i]['type'] = 'starter';
                    break;
                case 'own':
                    $newData['characterData'][$i]['type'] = 'own';
                    break;
            }
        }

        if (isset($data['breeding_items']) && is_array($data['breeding_items'])) {
            $newData['items'] = array_values(array_filter($data['breeding_items'], function ($item) {
                return isset($item['item_id']) && (int) $item['item_id'] > 0;
            }));
        }
        if ($tokens && count($tokens)) {
            $tokenItems = array_map(
                fn ($token) => ['item_id' => $token],
                $tokens
            );
            $newData['items'] = array_merge($newData['items'] ?? [], $tokenItems);
        }
        if (isset($data['type'])) {
            $newData['type'] = $data['type'] ?? 'Full';
        }
        if (isset($data['split_user']) && $data['split_user']) {
            $newData['split_user_id'] = (int) $data['split_user'];
            $newData['split_status'] = 'Pending';
        }

        return $newData;
    }

    private function fetchEditedItemStacks($items, $user, $previousItems) {
        $retainedItems = [];
        $newItems = [];

        foreach ($items as $row) {
            $stackId = (int) ($row['stack_id'] ?? 0);
            $previousItem = $previousItems[$stackId] ?? null;
            $previousItemId = is_array($previousItem)
                ? ($previousItem['item_id'] ?? null)
                : ($previousItem->item_id ?? null);

            if ($previousItem && (int) $previousItemId === (int) $row['item_id']) {
                $retainedItems[$stackId] = (array) $previousItem;
                continue;
            }

            unset($row['stack_id']);
            $newItems[] = $row;
        }

        $newStacks = $newItems ? $this->fetchItemStacks($newItems, $user, false) : [];
        $newItemStacks = $newStacks['user']['user_items'] ?? [];
        $userItems = $retainedItems + $newItemStacks;

        return $userItems ? ['user' => ['user_items' => $userItems]] : [];
    }

    private function fetchItemStacks($items, $user, $reserve = true) {
        $stacks = [];
        $requestedItems = [];

        foreach ($items as $row) {
            $itemId = (int) $row['item_id'];
            $requestedItems[$itemId]['quantity'] = ($requestedItems[$itemId]['quantity'] ?? 0) + 1;
            if (isset($row['value']) && $row['value']) {
                $requestedItems[$itemId]['value'] = $row['value'];
            }
        }

        foreach ($requestedItems as $itemId => $requestedItem) {
            $remaining = $requestedItem['quantity'];
            $maybeStacks = UserItem::where('user_id', $user->id)->where('item_id', $itemId)
                ->where('count', '>', 0)->get();

            foreach ($maybeStacks as $mStack) {
                $quantity = min($remaining, max(0, $mStack->availableQuantity));
                if ($quantity < 1) {
                    continue;
                }

                $stacks['user']['user_items'][$mStack->id] = [
                    'quantity'  => $quantity,
                    'item_id'   => $itemId,
                    ...(isset($requestedItem['value']) ? ['value' => $requestedItem['value']] : []),
                ];

                if ($reserve) {
                    $mStack->breeding_count += $quantity;
                    $mStack->save();
                }
                $remaining -= $quantity;

                if ($remaining < 1) {
                    break;
                }
            }

            if ($remaining > 0) {
                throw new \Exception('Not enough of item #'.$itemId.' is available for this breeding.');
            }
        }

        return $stacks;
    }

    private function syncEditedBreedingItemStacks($user, $previousItems, $newItems) {
        $previous = is_array($previousItems) ? $previousItems : [];
        $new = is_array($newItems) ? $newItems : [];
        $stackIds = array_unique(array_merge(array_keys($previous), array_keys($new)));

        foreach ($stackIds as $stackId) {
            $previousRow = $previous[$stackId] ?? null;
            $newRow = $new[$stackId] ?? null;
            $previousQuantity = $previousRow
                ? (int) (is_array($previousRow) ? ($previousRow['quantity'] ?? 1) : ($previousRow->quantity ?? 1))
                : 0;
            $newQuantity = $newRow
                ? (int) (is_array($newRow) ? ($newRow['quantity'] ?? 1) : ($newRow->quantity ?? 1))
                : 0;
            $quantityChange = $newQuantity - $previousQuantity;

            if ($quantityChange === 0) {
                continue;
            }

            $stack = UserItem::where('id', $stackId)->where('user_id', $user->id)->first();
            if (!$stack) {
                continue;
            }

            $stack->breeding_count = max(0, (int) $stack->breeding_count + $quantityChange);
            $stack->save();
        }
    }

    private function hasGreeds($items) {
        foreach ($items['user']['user_items'] as $stack_id => $qty) {
            $stack = UserItem::where('id', $stack_id)->first();
            if ($stack->item->name === 'Greed\'s Token') {
                return true;
            }
        }

        return false;
    }

    private function checkBreedingOpen() {
        $queue_status = Settings::get('breeding_queue_status') ?? 0;

        if ($queue_status == 0) {
            return false;
        } elseif ($queue_status == 1) {
            return Auth::check() && Auth::user()->isStaff;
        } else {
            return true;
        }
    }

    private function checkParentsBreedable($parent_1, $parent_2) {
        if ($parent_1->isBreedable && $parent_2->isBreedable) {
            return true;
        }

        return false;
    }

    private function removeItemsFromBreeding($breeding, $stacks) {
        $stacksToRemove = [];
        if ($stacks && count($stacks)) {
            $service = new InventoryManager;
            foreach ($stacks as $stack_id => $quantity) {
                $stack = UserItem::where('id', $stack_id)->first();
                if (!$stack) {
                    continue;
                }

                $stack->breeding_count = max(0, (int) $stack->breeding_count - (int) $quantity);
                $stack->save();

                $stacksToRemove[] = $stack_id;
            }
        }

        if ($stacksToRemove && count($stacksToRemove)) {
            $breedingData = $breeding->breedingData;
            if (isset($breedingData->user->user_items)) {
                $userItems = (array) $breedingData->user->user_items;
                foreach ($stacksToRemove as $stack_id) {
                    unset($userItems[$stack_id]);
                }
                $breedingData->user->user_items = $userItems;
            }
            $breeding->update([
                'breeding_data' => json_encode($breedingData),
            ]);
        }
    }

    private function markBreedingCurrencyUsed($breeding) {
        $currency = Currency::find(Settings::get('breeding_permission_currency'));
        if (!$currency) {
            throw new \Exception('Breeding permission currency is not configured.');
        }

        $parent1 = $breeding->parent1;
        $parent2 = $breeding->parent2;

        if ($parent1->user_id && (int) $parent1->user_id === (int) $breeding->user_id) {
            if (!$debit = (new CurrencyManager)->debitCurrency($parent1, null, 'Used in Breeding #'.$breeding->id, null, $currency, 1)) {
                throw new \Exception('Could not debit currency for breeding. Please contact an admin.');
            }
        }

        if ($parent2->user_id && (int) $parent2->user_id === (int) $breeding->user_id) {
            if (!$debit = (new CurrencyManager)->debitCurrency($parent2, null, 'Used in Breeding #'.$breeding->id, null, $currency, 1)) {
                throw new \Exception('Could not debit currency for breeding. Please contact an admin.');
            }
        }
    }

    private function formatMarkingsForCreation($markings, $side = null) {
        $glint_id = Settings::get('glint_id');
        $m = [
            'marking_id'        => [],
            'is_dominant'       => [],
            'side_id'           => [],
            'marking_color_0'   => [],
            'marking_color_1'   => [],
        ];
        //dd($markings);
        foreach ($markings as $group) {
            foreach ($group as $marking) {
                $is_glint = $marking['marking']->id == $glint_id;
                if ($is_glint) {
                    if ($marking['is_dom']) {
                        $base_1 = $marking['base'][0]->id;
                        $base_2 = $marking['base'][1]->id;
                    } else {
                        $base_1 = $marking['base']->id;
                    }
                }
                $m['marking_id'][] = $marking['marking']->id;
                $m['is_dominant'][] = $marking['is_dom'];
                $m['side_id'][] = $side;
                $m['marking_color_0'][] = $is_glint ? $base_1 : null;
                $m['marking_color_1'][] = $is_glint && isset($base_2) ? $base_2 : null;
            }
        }

        return $m;
    }

    /**
     * Check's the parent's 2 recent lines of lineage for inbreeding.
     *
     * @param mixed $data
     *
     * @return bool|int
     */
    private function checkInbreeding($data) {
        $parent_1 = $data->parent_1;
        $parent_2 = $data->parent_2;

        $p1_lin = $parent_1->lineage()->first([
            'sire_id',
            'sire_sire_id',
            'sire_dam_id',
            'dam_id',
            'dam_sire_id',
            'dam_dam_id',
        ])?->toArray() ?? [];
        $p2_lin = $parent_2->lineage()->first([
            'sire_id',
            'sire_sire_id',
            'sire_dam_id',
            'dam_id',
            'dam_sire_id',
            'dam_dam_id',
        ])?->toArray() ?? [];

        $p1_lin = array_filter($p1_lin, fn ($value) => !is_null($value));
        $p2_lin = array_filter($p2_lin, fn ($value) => !is_null($value));

        $parent_1->lineage = Character::findMany($p1_lin);
        $parent_2->lineage = Character::findMany($p2_lin);

        $common = array_intersect($p1_lin, $p2_lin);

        if ($common && count($common)) {
            return true;
        }

        return false;
    }
}
