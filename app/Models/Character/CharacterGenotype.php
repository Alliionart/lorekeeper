<?php

namespace App\Models\Character;

use App\Models\Model;

//use App\Models\Stat\Stat;

class CharacterGenotype extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'genotype',
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

    // public function stat() {
    //     return $this->belongsTo(Stat::class);
    // }
}
