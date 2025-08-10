<?php

namespace App\Models;

class SiteOptions extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'value',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'site_options';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'key'         => 'required|unique:site_options|between:3,100',
        'value'       => 'nullable|between:0,2048',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'key'         => 'required|unique:site_options|between:3,100',
        'value'       => 'nullable|between:0,2048',
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
