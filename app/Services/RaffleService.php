<?php

namespace App\Services;

use App\Models\Raffle\Raffle;
use App\Models\Raffle\RaffleGroup;
use App\Models\Raffle\RaffleRewards;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RaffleService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Raffle Service
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of raffles.
    |
    */

    /**
     * Creates a raffle.
     *
     * @param array $data
     *
     * @return Raffle
     */
    public function createRaffle($data) {
        DB::beginTransaction();
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        $raffle = Raffle::create(Arr::only($data, ['name', 'is_active', 'is_admin_only', 'winner_count', 'group_id', 'order', 'rewards_type']));
        $raffle->excluded_winners = $this->normalizeExcludedWinners($data['excluded_winners'] ?? [], $raffle);
        $raffle->save();
        DB::commit();

        return $raffle;
    }

    /**
     * Updates a raffle.
     *
     * @param array  $data
     * @param Raffle $raffle
     *
     * @return Raffle
     */
    public function updateRaffle($data, $raffle) {
        DB::beginTransaction();
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        $raffle->update(Arr::only($data, ['name', 'is_active', 'is_admin_only', 'winner_count', 'group_id', 'order', 'ticket_cap', 'rewards_type']));
        $raffle->excluded_winners = $this->normalizeExcludedWinners($data['excluded_winners'] ?? [], $raffle);
        $raffle->save();
        DB::commit();

        return $raffle;
    }

    /**
     * Deletes a raffle.
     *
     * @param Raffle $raffle
     *
     * @return bool
     */
    public function deleteRaffle($raffle) {
        DB::beginTransaction();
        foreach ($raffle->tickets as $ticket) {
            $ticket->delete();
        }
        $raffle->delete();
        DB::commit();

        return true;
    }

    /**
     * Creates a raffle group.
     *
     * @param array $data
     *
     * @return RaffleGroup
     */
    public function createRaffleGroup($data) {
        DB::beginTransaction();
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        $group = RaffleGroup::create(Arr::only($data, ['name', 'is_active']));
        DB::commit();

        return $group;
    }

    /**
     * Updates a raffle group.
     *
     * @param array $data
     * @param mixed $group
     *
     * @return Raffle
     */
    public function updateRaffleGroup($data, $group) {
        DB::beginTransaction();
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        $group->update(Arr::only($data, ['name', 'is_active']));
        foreach ($group->raffles as $raffle) {
            $raffle->update(['is_active' => $data['is_active']]);
        }
        DB::commit();

        return $group;
    }

    /**
     * Deletes a raffle group.
     *
     * @param mixed $group
     *
     * @return bool
     */
    public function deleteRaffleGroup($group) {
        DB::beginTransaction();
        foreach ($group->raffles as $raffle) {
            $raffle->update(['group_id' => null]);
        }
        $group->delete();
        DB::commit();

        return true;
    }

    /* *********************************
    * Raffle Rewards Management
    *********************************/

    /**
     * @param mixed $raffle
     * @param mixed $data
     * @param mixed $user
     */
    public function editRaffleRewards($raffle, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$user->isStaff || !$user->hasPower('edit_raffles')) {
                throw new \Exception('Only staff or the owner of the raffle may edit.');
            }
            if (!$raffle) {
                throw new \Exception('Raffle not found.');
            }

            $raffle->rewards()->delete();

            switch ($raffle->rewards_type) {
                case 'Static':
                default:
                    $data = $this->handleStaticRewards($raffle, $data);
                    break;
                case 'Random':
                    $data = $this->handleRandomRewards($raffle, $data);
                    break;
                case 'Tiered':
                    $data = $this->handleTieredRewards($raffle, $data);
                    break;
            }

            $new = [];
            foreach ($data as $reward) {
                $new[] = RaffleRewards::create($reward);
            }

            return $this->commitReturn($raffle);
        } catch (\Exception $e) {
            dd($e);
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * @param mixed $raffle
     * @param mixed $data
     */
    public function handleStaticRewards($raffle, $data) {
        $newData = [];
        if ($data['rewardable_type']) {
            foreach ($data['rewardable_type'] as $i => $type) {
                $newData[$i] = [
                    'raffle_id'         => $raffle->id,
                    'rewardable_type'   => $type,
                    'rewardable_id'     => (int) $data['rewardable_id'][$i] ?? null,
                    'quantity'          => (int) $data['quantity'][$i] ?? 1,
                    'data'              => $data['data'][$i] ?? null,
                ];
            }
        }

        return $newData;
    }

    public function handleRandomRewards($raffle, $data) {
        $newData = [];
        if ($data['rewardable_type']) {
            foreach ($data['rewardable_type'] as $i => $type) {
                $newData[$i] = [
                    'raffle_id'         => $raffle->id,
                    'rewardable_type'   => $type,
                    'rewardable_id'     => (int) $data['rewardable_id'][$i] ?? null,
                    'quantity'          => (int) $data['quantity'][$i] ?? 1,
                    'weight'            => (float) $data['weight'][$i] ?? 1,
                    'data'              => $data['data'][$i] ?? null,
                ];
            }
        }

        return $newData;
    }

    public function handleTieredRewards($raffle, $data) {
        $newData = [];
        if ($data['tiers']) {
            foreach ($data['tiers'] as $tierIndex => $tier) {
                if (isset($tier['rewards']) && is_array($tier['rewards'])) {
                    foreach ($tier['rewards'] as $rewardIndex => $reward) {
                        $newData[] = [
                            'raffle_id'         => $raffle->id,
                            'position'          => $tier['tier'] ?? null,
                            'opt_out'           => isset($tier['opt_out']) ? (bool) $tier['opt_out'] : false,
                            'rewardable_type'   => $reward['rewardable_type'] ?? null,
                            'rewardable_id'     => (int) ($reward['rewardable_id'] ?? null),
                            'quantity'          => (int) ($reward['quantity'] ?? 1),
                            'weight'            => (float) ($reward['weight'] ?? 1),
                            'data'              => $reward['data'] ?? null,
                        ];
                    }
                }
            }
        }

        return $newData;
    }

    /**
     * Validates and normalizes raffle and reward exclusions.
     *
     * @param array  $entries
     * @param Raffle $raffle
     *
     * @return array
     */
    private function normalizeExcludedWinners($entries, $raffle) {
        $rewardIds = $raffle->rewards()->pluck('id')->map(function ($id) {
            return (string) $id;
        })->all();
        $normalized = [];

        foreach ((array) $entries as $entry) {
            $rewardId = $entry['reward_id'] ?? null;
            $userIds = collect($entry['user_ids'] ?? [])->filter()->unique()->take(20)->map(function ($id) {
                return (int) $id;
            })->values()->all();

            if (!$rewardId || !$userIds || ($rewardId !== 'all' && !in_array((string) $rewardId, $rewardIds, true))) {
                continue;
            }

            $normalized[] = [
                'reward_id' => $rewardId === 'all' ? 'all' : (int) $rewardId,
                'user_ids'  => $userIds,
            ];
        }

        return $normalized;
    }
}
