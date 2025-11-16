<?php

namespace App\Models\Guild;

use App\Models\Model;

class GuildRank extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'guild_id', 'name', 'level', 'required_reputation', 'description', 'for_character', 'for_user', 'has_image', 'hash',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guild_ranks';

    /**
     * Validation rules for guild rank creation.
     *
     * @var array
     */
    public static $createRules = [
        'guild_id'           => 'required',
        'name'               => 'required|unique:guild_ranks|between:3,100',
        'level'              => 'nullable',
        'required_reputation'=> 'min:0',
        'description'        => 'nullable',
        'image'              => 'mimes:png',
    ];

    /**
     * Validation rules for guild rank updating.
     *
     * @var array
     */
    public static $updateRules = [
        'guild_id'           => 'required',
        'name'               => 'required|unique:guild_ranks|between:3,100',
        'level'              => 'nullable',
        'required_reputation'=> 'min:0',
        'description'        => 'nullable',
        'image'              => 'mimes:png',
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
     * Get the characters associated with this guild rank.
     */
    public function characters() {
        return $this->hasMany(GuildCharacter::class, 'rank_id');
    }

    /**
     * Get the users associated with this guild rank.
     */
    public function users() {
        return $this->hasMany(GuildMember::class, 'rank_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/
}
