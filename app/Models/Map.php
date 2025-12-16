<?php

namespace App\Models;

class Map extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'type', 'description', 'latitude', 'longitude', 'icon', 'url',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'map_data';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|between:3,100',
        'type'        => 'required|between:3,100',
        'description' => 'nullable',
        'latitude'    => 'nullable|numeric',
        'longitude'   => 'nullable|numeric',
        'icon'        => 'nullable|between:3,100',
        'url'         => 'nullable|between:3,100',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'        => 'required|between:3,100',
        'type'        => 'required|between:3,100',
        'description' => 'nullable',
        'latitude'    => 'nullable|numeric',
        'longitude'   => 'nullable|numeric',
        'icon'        => 'nullable|between:3,100',
        'url'         => 'nullable|between:3,100',
    ];

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/map/edit-item/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }
}
