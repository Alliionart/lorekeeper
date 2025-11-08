<?php

namespace App\Models\User;

use App\Models\Model;

class UserDiscordLevel extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'exp', 'level', 'last_message_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_discord_levels';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user this set of settings belongs to.
     */
    public function user() {
        return $this->belongsTo('App\Models\User\User');
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /*
     * Calculates a user's relative "rank" among all users on the site.
     *
     * @param int $user
     *
     * @return int
     */
    public function relativeRank($user) {
        $orderedLevels = $this->query()->orderBy('level', 'DESC')->orderBy('exp', 'DESC')->get();
        $rankIndex = $orderedLevels->search(function ($level) use ($user) {
            return $user->id == $level->user_id;
        });

        return $rankIndex + 1;
    }

    /*
     * Calculates the top ten among users on the site.
     *
     * @return int
     */
    public function topTen() {
        $orderedLevels = $this->query()->orderBy('level', 'DESC')->orderBy('exp', 'DESC')->take(10)->get();

        return $orderedLevels;
    }
}
