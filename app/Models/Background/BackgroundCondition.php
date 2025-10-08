<?php

namespace App\Models\Background;

use App\Models\Model;

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
        'value'               => 'nullable'
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
        'value'               => 'nullable'
    ];
    

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
}
