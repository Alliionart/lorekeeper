<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Models\Raffle\Raffle;
use App\Models\Raffle\RaffleTicket;
use App\Models\User\User;
use App\Services\Claymore\GearManager;
use App\Services\Claymore\WeaponManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RaffleManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Raffle Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of raffle ticket data.
    |
    */

    /**
     * Adds tickets to a raffle.
     *
     * @param Raffle $raffle
     * @param array  $data
     *
     * @return int
     */
    public function addTickets($raffle, $data) {
        $count = 0;
        foreach ($data['user_id'] as $key=> $id) {
            if ($user = User::where('id', $id)->first()) {
                if ($this->addTicket($user, $raffle, $data['ticket_count'][$key])) {
                    $count += $data['ticket_count'][$key];
                }
            } else {
                if ($this->addTicket($data['alias'][$key], $raffle, $data['ticket_count'][$key])) {
                    $count += $data['ticket_count'][$key];
                }
            }
        }

        return $count;
    }

    /**
     * Adds one or more tickets to a single user for a raffle.
     *
     * @param User   $user
     * @param Raffle $raffle
     * @param int    $count
     *
     * @return int
     */
    public function addTicket($user, $raffle, $count = 1) {
        if (!$user) {
            return 0;
        } elseif (!$raffle) {
            return 0;
        } elseif ($count == 0) {
            return 0;
        } elseif ($raffle->rolled_at != null) {
            return 0;
        } elseif ($user instanceof User && $this->isUserExcludedFromRaffle($user, $raffle)) {
            $this->setError('error', 'This user is excluded from the entire raffle or all of its rewards.');

            return 0;
        } elseif ($raffle->ticket_cap > 0 && ((is_string($user) ? $raffle->tickets()->where('alias', $user)->count() : $raffle->tickets()->where('user_id', $user->id)->count()) > $raffle->ticket_cap || (is_string($user) ? $raffle->tickets()->where('alias', $user)->count() : $raffle->tickets()->where('user_id', $user->id)->count()) + $count > $raffle->ticket_cap)) {
            return 0;
        } else {
            DB::beginTransaction();
            $data = ['raffle_id' => $raffle->id, 'created_at' => Carbon::now()] + (is_string($user) ? ['alias' => $user] : ['user_id' => $user->id]);
            for ($i = 0; $i < $count; $i++) {
                RaffleTicket::create($data);
            }
            DB::commit();

            return 1;
        }

        return 0;
    }

    /**
     * Updates the Tiered raffle rewards a user opts out of.
     *
     * @param Raffle $raffle
     * @param User   $user
     * @param array  $optedOut
     *
     * @return bool
     */
    public function updateOptedOutRewards($raffle, $user, $optedOut) {
        if (!$raffle || $raffle->rewards_type !== 'Tiered' || $raffle->is_active != 1 || $raffle->rolled_at) {
            return false;
        }

        $tickets = $raffle->tickets()->where('user_id', $user->id)->get();
        if ($tickets->isEmpty()) {
            return false;
        }

        $availableRewards = $raffle->rewards->mapWithKeys(function ($reward) {
            $key = $reward->rewardable_type.':'.$reward->rewardable_id;

            return [$key => [
                'rewardable_type' => $reward->rewardable_type,
                'rewardable_id'   => (int) $reward->rewardable_id,
            ]];
        });

        $selectedRewards = collect($optedOut)->unique()->map(function ($key) use ($availableRewards) {
            return $availableRewards->get($key);
        })->filter()->values()->all();

        foreach ($tickets as $ticket) {
            $ticket->opted_out = $selectedRewards;
            $ticket->save();
        }

        return true;
    }

    /**
     * Removes a single ticket.
     *
     * @param RaffleTicket $ticket
     *
     * @return bool
     */
    public function removeTicket($ticket) {
        if (!$ticket) {
            return null;
        } else {
            $ticket->delete();

            return true;
        }

        return false;
    }

    /**
     * Rolls a raffle group consecutively.
     * If the $updateGroup flag is true, winners will be removed
     * from other raffles in the group.
     *
     * @param \App\Models\Raffle\RaffleGroup $raffleGroup
     * @param bool                           $updateGroup
     *
     * @return bool
     */
    public function rollRaffleGroup($raffleGroup, $updateGroup = true) {
        if (!$raffleGroup) {
            return null;
        }
        DB::beginTransaction();
        foreach ($raffleGroup->raffles()->orderBy('order')->get() as $raffle) {
            if (!$this->rollRaffle($raffle, $updateGroup)) {
                DB::rollback();

                return false;
            }
        }
        $raffleGroup->is_active = 2;
        $raffleGroup->save();
        DB::commit();

        return true;
    }

    /**
     * Rolls a single raffle and marks it as completed.
     * If the $updateGroup flag is true, winners will be removed
     * from other raffles in the group.
     *
     * @param Raffle $raffle
     * @param bool   $updateGroup
     *
     * @return bool
     */
    public function rollRaffle($raffle, $updateGroup = false) {
        if (!$raffle) {
            return null;
        }
        DB::beginTransaction();
        // roll winners
        if ($winners = $this->rollWinners($raffle)) {
            // mark raffle as finished
            $raffle->is_active = 2;
            $raffle->rolled_at = Carbon::now();
            $raffle->save();

            // updates the raffle group if necessary
            if ($updateGroup && !$this->afterRoll($winners, $raffle->group, $raffle)) {
                DB::rollback();

                return false;
            }
            DB::commit();

            return true;
        }
        DB::rollback();

        return false;
    }

    /**
     * Distributes the rewards for the winners of the raffle.
     *
     * @param array  $winners
     * @param Raffle $raffle
     */
    public function rewardWinners($winners, $raffle) {
        $rewards = $raffle->rewards;
        foreach ($winners as $winner) {
            $user = $winner->user;

            if ($raffle->rewards_type === 'Random') {
                $eligibleRewards = $rewards->reject(function ($reward) use ($raffle, $user) {
                    return $user && $this->isRewardExcludedForUser($raffle, $reward, $user->id);
                });
                $reward = $this->selectWeightedReward($eligibleRewards);
                $winnerRewards = $reward ? collect([$reward]) : collect();
            } elseif ($raffle->rewards_type === 'Tiered') {
                $optedOut = collect($winner->opted_out ?? [])->map(function ($reward) {
                    return $reward['rewardable_type'].':'.$reward['rewardable_id'];
                });
                $winnerRewards = $rewards->filter(function ($reward) use ($winner) {
                    return $this->positionMatches($reward->position, $winner->position);
                })->reject(function ($reward) use ($optedOut) {
                    return $optedOut->contains($reward->rewardable_type.':'.$reward->rewardable_id);
                })->reject(function ($reward) use ($raffle, $user) {
                    return $user && $this->isRewardExcludedForUser($raffle, $reward, $user->id);
                });
            } else {
                $winnerRewards = $rewards->reject(function ($reward) use ($raffle, $user) {
                    return $user && $this->isRewardExcludedForUser($raffle, $reward, $user->id);
                });
            }

            $winner->winner_data = $winnerRewards->map(function ($reward) {
                return [
                    'rewardable_type' => $reward->rewardable_type,
                    'rewardable_id'   => (int) $reward->rewardable_id,
                    'quantity'        => (int) $reward->quantity,
                ];
            })->values()->all();
            $winner->save();

            if (!$user || $winnerRewards->isEmpty()) {
                continue;
            }

            foreach ($winnerRewards as $reward) {
                if (!$this->grantReward($reward, $user, $raffle)) {
                    return false;
                }
            }

            $rewardNames = $winnerRewards->map(function ($reward) {
                return 'x'.$reward->quantity.' '.$reward->reward->displayName.($reward->rewardable_type === 'BreedingPermission' ? ' breeding permission(s)' : '');
            });
            if (!Notifications::create('RAFFLE_REWARD', $user, [
                'raffle_id'   => $raffle->id,
                'raffle_name' => $raffle->name,
                'rewards'     => $rewardNames->implode(', '),
            ])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks whether a user is excluded from the raffle or every reward.
     *
     * @param mixed $user
     * @param mixed $raffle
     */
    private function isUserExcludedFromRaffle($user, $raffle) {
        $exclusions = collect($raffle->excluded_winners ?? []);
        $userId = (int) $user->id;

        if ($exclusions->contains(function ($exclusion) use ($userId) {
            return ($exclusion['reward_id'] ?? null) === 'all'
                && in_array($userId, array_map('intval', $exclusion['user_ids'] ?? []), true);
        })) {
            return true;
        }

        $rewardIds = $raffle->rewards()->pluck('id')->map(function ($id) {
            return (int) $id;
        });
        if ($rewardIds->isEmpty()) {
            return false;
        }

        $excludedRewardIds = $exclusions->filter(function ($exclusion) use ($userId) {
            return ($exclusion['reward_id'] ?? null) !== 'all'
                && in_array($userId, array_map('intval', $exclusion['user_ids'] ?? []), true);
        })->pluck('reward_id')->map(function ($id) {
            return (int) $id;
        })->unique();

        return $rewardIds->diff($excludedRewardIds)->isEmpty();
    }

    /**
     * Rolls the winners of a raffle.
     *
     * @param Raffle $raffle
     *
     * @return array
     */
    private function rollWinners($raffle) {
        $excludedUserIds = collect($raffle->excluded_winners ?? [])
            ->filter(function ($exclusion) {
                return ($exclusion['reward_id'] ?? null) === 'all';
            })
            ->flatMap(function ($exclusion) {
                return $exclusion['user_ids'] ?? [];
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique();
        $ticketPool = $raffle->tickets->reject(function ($ticket) use ($excludedUserIds) {
            return $ticket->user_id && $excludedUserIds->contains((int) $ticket->user_id);
        })->values();
        $ticketCount = $ticketPool->count();
        $winners = ['ids' => [], 'aliases' => []];
        $winnersRolled = [];
        for ($i = 0; $i < $raffle->winner_count; $i++) {
            if ($ticketCount == 0) {
                break;
            }

            $num = mt_rand(0, $ticketCount - 1);
            $winner = $ticketPool[$num];
            $winnersRolled[] = $winner;

            // save ticket position as ($i + 1)
            $winner->update(['position' => $i + 1]);

            // save the winning ticket's user id
            if (isset($winner->user_id)) {
                $winners['ids'][] = $winner->user_id;
            } else {
                $winners['aliases'][] = $winner->alias;
            }

            // remove ticket from the ticket pool after pulled
            $ticketPool->forget($num);
            $ticketPool = $ticketPool->values();

            $ticketCount--;

            // remove tickets for the same user...I'm unsure how this is going to hold up with 3000 tickets,
            foreach ($ticketPool as $key=> $ticket) {
                if (($ticket->user_id != null && $ticket->user_id == $winner->user_id) || ($ticket->user_id == null && $ticket->alias == $winner->alias)) {
                    $ticketPool->forget($key);
                }
            }
            $ticketPool = $ticketPool->values();
            $ticketCount = $ticketPool->count();
        }

        if (!$this->rewardWinners($winnersRolled, $raffle)) {
            return false;
        }

        return $winners;
    }

    /**
     * Maps the data attributes to the rewardable type.
     *
     * @param mixed      $data
     * @param mixed      $rewardable_type
     * @param mixed|null $raffle
     */
    private function mapData($data, $rewardable_type, $raffle = null) {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        $data = is_array($data) ? $data : [];

        if ($rewardable_type === 'BreedingPermission') {
            $mappedData = [
                'description'   => 'Granted from Raffle: '.($data['notes'] ?? $data['description'] ?? ''),
                'type'          => 'Full',
            ];

            return $mappedData;
        }
        if (in_array($rewardable_type, ['Item', 'Gear', 'Weapon', 'Pet'])) {
            $mappedData = [
                'data'   => 'Granted from Raffle: '.$raffle->name,
                'notes'  => $data['notes'] ?? $data['description'] ?? '',
            ];

            return $mappedData;
        }

        return $data;
    }

    /**
     * Selects one reward using its configured weight.
     *
     * @param mixed $rewards
     */
    private function selectWeightedReward($rewards) {
        $totalWeight = $rewards->sum(function ($reward) {
            return max(0, (int) $reward->weight);
        });
        if ($totalWeight < 1) {
            return null;
        }

        $roll = random_int(1, $totalWeight);
        foreach ($rewards as $reward) {
            $roll -= max(0, (int) $reward->weight);
            if ($roll <= 0) {
                return $reward;
            }
        }

        return null;
    }

    /**
     * Checks whether a user is excluded from a specific reward row.
     *
     * @param mixed $raffle
     * @param mixed $reward
     * @param mixed $userId
     */
    private function isRewardExcludedForUser($raffle, $reward, $userId) {
        foreach ($raffle->excluded_winners ?? [] as $exclusion) {
            if (($exclusion['reward_id'] ?? null) !== 'all'
                && (int) ($exclusion['reward_id'] ?? 0) === (int) $reward->id
                && in_array((int) $userId, array_map('intval', $exclusion['user_ids'] ?? []), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether a winning position is included in a tier definition.
     *
     * @param mixed $tier
     * @param mixed $position
     */
    private function positionMatches($tier, $position) {
        foreach (preg_split('/\s*,\s*/', (string) $tier) as $positionRange) {
            if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $positionRange, $matches)) {
                if ($position >= min((int) $matches[1], (int) $matches[2]) && $position <= max((int) $matches[1], (int) $matches[2])) {
                    return true;
                }
            } elseif (is_numeric($positionRange) && (int) $positionRange === (int) $position) {
                return true;
            }
        }

        return false;
    }

    /**
     * Grants one configured reward to a raffle winner.
     *
     * @param mixed $reward
     * @param mixed $user
     * @param mixed $raffle
     */
    private function grantReward($reward, $user, $raffle) {
        $asset = $reward->reward;
        if (!$asset) {
            return false;
        }

        $data = $this->mapData($reward->data, $reward->rewardable_type, $raffle);
        $quantity = (int) $reward->quantity;

        switch ($reward->rewardable_type) {
            case 'Item':
                return (new InventoryManager)->creditItem(null, $user, null, $data, $asset, $quantity);
            case 'Currency':
                return (new CurrencyManager)->creditCurrency(null, $user, 'Raffle Reward', 'Received from raffle: '.$raffle->name, $asset, $quantity);
            case 'Pet':
                return (new PetManager)->creditPet(null, $user, null, $data, $asset, $quantity, $data['variant'] ?? null, $data['evolution'] ?? null);
            case 'Gear':
                return (new GearManager)->creditGear(null, $user, null, $data, $asset, $quantity);
            case 'Weapon':
                return (new WeaponManager)->creditWeapon(null, $user, null, $data, $asset, $quantity);
            case 'BreedingPermission':
                $permissionData = $data + [
                    'recipient_id'  => $user->id,
                    'quantity'      => $quantity,
                ];

                return (new CharacterManager)->createBreedingPermission($permissionData, $asset, Auth::user());
        }

        return false;
    }

    /**
     * Rolls the winners of a raffle.
     *
     * @param array                          $winners
     * @param \App\Models\Raffle\RaffleGroup $raffleGroup
     * @param Raffle                         $raffle
     *
     * @return bool
     */
    private function afterRoll($winners, $raffleGroup, $raffle) {
        // remove any tickets from winners in raffles in the group that aren't completed
        $raffles = $raffleGroup->raffles()->where('is_active', '!=', 2)->where('id', '!=', $raffle->id)->get();
        foreach ($raffles as $r) {
            $r->tickets()->where(function ($query) use ($winners) {
                $query->whereIn('user_id', $winners['ids'])->orWhereIn('alias', $winners['aliases']);
            })->delete();
        }

        return true;
    }
}
