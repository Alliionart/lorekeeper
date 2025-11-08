<?php

namespace App\Models\Discord;

use App\Models\Model;

class DiscordReward extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'level', 'loot', 'role_reward_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'discord_rewards';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'level' => 'required|integer|min:1|unique:discord_rewards',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'level' => 'required|integer|min:1',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the rewards.
     */
    public function getRewardsAttribute() {
        if ($this->loot) {
            $assets = parseDiscordAssetData(json_decode($this->loot));
            $rewards = [];
            foreach ($assets as $type => $a) {
                $class = getAssetModelString($type, false);
                foreach ($a as $id => $asset) {
                    $rewards[] = (object) [
                        'rewardable_type' => $class,
                        'rewardable_id'   => $id,
                        'quantity'        => $asset['quantity'],
                    ];
                }
            }

            return $rewards;
        } else {
            return null;
        }
    }
}
