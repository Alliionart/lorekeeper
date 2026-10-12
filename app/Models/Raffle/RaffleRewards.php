<?php

namespace App\Models\Raffle;

use App\Models\Character\Character;
use App\Models\Claymore\Gear;
use App\Models\Claymore\Weapon;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Model;
use App\Models\Pet\Pet;

class RaffleRewards extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'raffle_id',
        'position',
        'rewardable_type',
        'rewardable_id',
        'quantity',
        'weight',
        'data',
        'opt_out',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'raffle_rewards';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'rewardable_type' => 'required',
        'rewardable_id'   => 'required',
        'quantity'        => 'required|integer|min:1',
        'weight'          => 'required|integer|min:1',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'rewardable_type' => 'required',
        'rewardable_id'   => 'required',
        'quantity'        => 'required|integer|min:1',
        'weight'          => 'required|integer|min:1',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    public function raffle() {
        return $this->belongsTo(Raffle::class, 'raffle_id');
    }

    /**
     * Get the reward attached to the loot entry.
     */
    public function reward() {
        switch ($this->rewardable_type) {
            case 'Item':
                return $this->belongsTo(Item::class, 'rewardable_id');
            case 'Currency':
                return $this->belongsTo(Currency::class, 'rewardable_id');
            case 'Pet':
                return $this->belongsTo(Pet::class, 'rewardable_id');
            case 'Gear':
                return $this->belongsTo(Gear::class, 'rewardable_id');
            case 'Weapon':
                return $this->belongsTo(Weapon::class, 'rewardable_id');
            case 'None':
                // Laravel requires a relationship instance to be returned (cannot return null), so returning one that doesn't exist here.
                return $this->belongsTo(self::class, 'rewardable_id', 'loot_table_id')->whereNull('loot_table_id');
            case 'BreedingPermission':
                return $this->belongsTo(Character::class, 'rewardable_id');
        }

        return null;
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDatasAttribute() {
        if (!$this->attributes['data']) {
            return null;
        }

        return json_decode($this->attributes['data'], true);
    }

    /**
     * Display the loot item and link to it's encylopedia entry.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        // Adds spaces to the rewardable_type between capital letters
        $displayType = preg_replace('/(?<!\ )[A-Z]/', ' $0', $this->rewardable_type);

        if ($this->rewardable_type == 'None') {
            return 'No Loot Drop';
        } else {
            return $displayType.' : <a href="'.$this->reward->url.'">'.$this->reward->name.'</a>';
        }
    }

    /**
     * Displays the drop rate of a loot.
     *
     * @return string
     */
    public function getDropRateAttribute() {
        $totalWeight = self::where('raffle_id', $this->raffle_id)->sum('weight');
        $dropRate = $this->weight / $totalWeight * 100;

        return number_format((float) $dropRate, 2, '.', '').'%';
    }
}
