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
        'image'       => 'mimes:png,jpeg,jpg,zip',
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
        'image'       => 'mimes:png,jpeg,jpg,zip',
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

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        if ($this->map_id) {
            //If this is a sub-item of a map, use the parent map's ID
            return 'images/data/maps/'.$this->map_id.'/'.$this->type;
        }

        return 'images/data/maps/'.$this->id;
    }

    /**
     * Gets the file directory containing the map's tile images.
     *
     * @return string
     */
    public function getTileImageDirectoryAttribute() {
        if ($this->map_id) {
            $map_id = $this->map_id;
        } else {
            $map_id = $this->id;
        }

        return 'images/data/maps/'.$map_id.'/tiles';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->id.'-.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the latitude/longitude of the map as a string.
     *
     * @return string
     */
    public function getLatLngAttribute() {
        return $this->latitude.','.$this->longitude;
    }
}
