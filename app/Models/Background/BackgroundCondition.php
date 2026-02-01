<?php

namespace App\Models\Background;

use App\Models\Model;
use App\Models\Species\Species;
use App\Models\Recipe\Recipe;

class BackgroundCondition extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'background_id', 'location', 'type', 'value',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'background_conditions';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'background_id'       => 'required',
        'location'            => 'required',
        'type'                => 'nullable',
        'value'               => 'nullable',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'background_id'       => 'required',
        'location'            => 'required',
        'type'                => 'nullable',
        'value'               => 'nullable',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Gets the species associated with this condition.
     */
    public function species() {
        return $this->belongsTo(Species::class, 'species_id', 'id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }

    /** 
     * Find out if the condition with 'Item' is craftable via recipe.
     */
    public function getIsCraftableAttribute() {
        if($this->type != 'Item') return false;

        $item_id = $this->value;

        //Find recipes where the output JSON contains the id as a key.
        $query = Recipe::whereRaw("JSON_EXTRACT(output, '$.items.\"{$item_id}\"') IS NOT NULL");

        return $query->count() > 0;
    }
}
