<?php

namespace App\Models\Character;

use App\Models\Character\CharacterClass;
use App\Models\Character\CharacterClassType;
use App\Models\Character;
use App\Models\Model;

class CharacterClassAssignment extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'class_id', 'chosen_ability',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_class_assignments';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['class'];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the image associated with this record.
     */
    public function character() {
        return $this->belongsTo(Character::class, 'character_id');
    }

    /**
     * Get the feature (character trait) associated with this record.
     */
    public function class() {
        return $this->belongsTo(CharacterClass::class, 'class_id');
    }

    /**********************************************************************************************

        Other Functions

    **********************************************************************************************/

    
}
