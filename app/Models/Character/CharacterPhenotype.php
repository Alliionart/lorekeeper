<?php

namespace App\Models\Character;

use App\Models\Model;

class CharacterPhenotype extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'phenotype',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'characters';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    public function character() {
        return $this->belongsTo(Character::class);
    }
}
