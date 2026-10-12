<?php

namespace App\Models\Raffle;

use App\Models\Model;
use App\Models\User\User;

class Raffle extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'is_active', 'is_admin_only', 'winner_count', 'group_id', 'order', 'ticket_cap', 'excluded_winners', 'rewards_type',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'raffles';
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'rolled_at'        => 'datetime',
        'excluded_winners' => 'array',
    ];

    /**
     * Accessors to append to the model.
     *
     * @var array
     */
    public $appends = ['name_with_group'];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the raffle tickets attached to this raffle.
     */
    public function tickets() {
        return $this->hasMany(RaffleTicket::class);
    }

    /**
     * Get the group that this raffle belongs to.
     */
    public function group() {
        return $this->belongsTo(RaffleGroup::class, 'group_id');
    }

    /**
     * Get the rewards data for this raffle.
     */
    public function rewards() {
        return $this->hasMany(RaffleRewards::class, 'raffle_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the raffle's name, linked to the raffle page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return $this->displayName();
    }

    /**
     * Get the name of the raffle, including group name if there is one.
     *
     * @return string
     */
    public function getNameWithGroupAttribute() {
        return ($this->group_id ? '['.$this->group->name.'] ' : '').$this->name;
    }

    /**
     * Gets the raffle's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'raffle_tickets';
    }

    /**
     * Gets the raffle's url.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('raffles/view/'.$this->id);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/raffles'); // Raffles are edited via a modal so don't have a unique raffle edit page
    }

    /**
     * Gets the raffle's admin rewards url.
     *
     * @return string
     */
    public function getAdminRewardsUrlAttribute() {
        return url('admin/raffles/'.$this->id.'/rewards');
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'manage_raffles';
    }

    public function getExcludedWinnersAttribute() {
        $excluded = $this->attributes['excluded_winners'] ?? null;
        if (is_string($excluded)) {
            $excluded = json_decode($excluded, true);
        }
        if (!is_array($excluded)) {
            return [];
        }

        return collect($excluded)->filter(function ($entry) {
            return is_array($entry);
        })->map(function ($entry) {
            $rewardId = $entry['reward_id'] ?? null;
            $userIds = collect($entry['user_ids'] ?? [])->filter()->unique()->values();

            $entry['reward_id'] = $rewardId;
            $entry['user_ids'] = $userIds->all();
            $entry['reward'] = $rewardId === 'all'
                ? 'all'
                : $this->rewards()->find($rewardId);
            $entry['users'] = User::whereIn('id', $userIds)->get();

            return $entry;
        })->values()->all();
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Displays the raffle's name, linked to the raffle page.
     *
     * @param mixed $asReward
     *
     * @return string
     */
    public function displayName($asReward = true) {
        return '<a href="'.$this->url.'" class="display-raffle">'.$this->name.($asReward ? ' (Raffle Ticket)' : '').'</a>';
    }
}
