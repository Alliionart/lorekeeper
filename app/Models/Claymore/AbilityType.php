<?php

namespace App\Models\Claymore;

use App\Models\Model;

class AbilityType extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'action'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ability_types';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:ability_types|between:3,25',
        'action'      => 'nullable',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'        => 'required|unique:ability_types|between:3,25',
        'action'      => 'nullable',
    ];

    /**********************************************************************************************

        RELATIONSHIPS

    **********************************************************************************************/

    /**
     * gets all abilities of this category.
     */
    public function gears() {
        return $this->hasMany(Ability::class, 'type_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/



    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-category">'.$this->name.'</a>';
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/ability_types?name='.$this->name);
    }

    /**
     * Gets the URL for an encyclopedia search of gears in this category.
     *
     * @return string
     */
    public function getSearchUrlAttribute() {
        return url('world/abilities?ability_types='.$this->id);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/abilities/types/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_claymores';
    }
}
