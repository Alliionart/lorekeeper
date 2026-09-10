<?php

namespace App\Models\Guild;

use App\Models\Character\Character;
use App\Models\Model;

class GuildCharacter extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'guild_id', 'character_id', 'rank_id', 'reputation', 'joined_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guild_characters';

    protected $casts = [
        'joined_at' => 'datetime',
    ];

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
     * Get the guild.
     */
    public function guild() {
        return $this->belongsTo(Guild::class, 'guild_id');
    }

    /**
     * Get the item associated with this item stack.
     */
    public function character() {
        return $this->belongsTo(Character::class, 'character_id');
    }

    /**
     * Get the guild rank associated with the character.
     */
    public function rank() {
        return $this->belongsTo(GuildRank::class, 'rank_id')
            ->where('guild_id', $this->guild_id);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/
}
