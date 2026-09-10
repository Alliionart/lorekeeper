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

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/guilds/'.$this->guild->id;
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    public function getImageUrlAttribute() {
        return asset($this->imageDirectory.'/'.($this->for_user ? 'user' : 'character').'_rank_'.$this->id.'.png');
    }

    /**
     * Gets the display name for the rank.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<span class="d-flex align-items-center"><img class="mr-2" style="max-width:25px;" src="'.$this->imageUrl.'" loading="lazy"/> '.$this->name.'</span>';
    }

    public function getRankImageNameAttribute() {
        return ($this->for_user ? 'user' : 'character').'_rank_'.$this->id.'.png';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    public function getRankImageName($id) {
        return ($this->for_user ? 'user' : 'character').'_rank_'.$id.'.png';
    }

    public function getRankImagePath($id) {
        return $this->imageDirectory.'/'.($this->for_user ? 'user' : 'character').'_rank_'.$this->id.'.png';
    }
}
