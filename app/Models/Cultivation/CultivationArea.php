<?php

namespace App\Models\Cultivation;

use Config;
use DB;
use App\Models\Model;


class CultivationArea extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'parsed_description', 'background_extension', 'plot_extension', 'max_plots', 'is_active'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cultivation_area';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|unique:items|between:3,100',
        'description' => 'nullable',
        'background_image' => 'mimes:png,jpg',
        'plot_image' => 'mimes:png,jpg',
        'max_plots' => 'nullable|integer|min:1',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required|unique:items|between:3,100',
        'description' => 'nullable',
        'background_image' => 'mimes:png,jpg',
        'plot_image' => 'mimes:png,jpg',
        'max_plots' => 'nullable|integer|min:1',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the plots that are allowed to be created here.
     */
    public function allowedPlots()
    {
        return $this->belongsToMany('App\Models\Cultivation\CultivationPlot', 'plot_area', 'area_id', 'plot_id');
    }

    
    /**
     * Get the plot areas.
     */
    public function plotAreas()
    {
        return $this->hasMany('App\Models\Cultivation\PlotArea', 'area_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort items in alphabetical order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool                                   $reverse
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false)
    {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort items by newest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query)
    {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort features oldest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query)
    {
        return $query->orderBy('id');
    }


    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return '<a href="'.$this->url.'">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/areas';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getBackgroundImageFileNameAttribute()
    {
        return $this->id . '-background.'.$this->background_extension;
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getPlotImageFileNameAttribute()
    {
        return $this->id . '-plot.'.$this->plot_extension;
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute()
    {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getBackgroundImageUrlAttribute()
    {
        if (!$this->background_extension) return "/images/area.png";
        return asset($this->imageDirectory . '/' . $this->backgroundImageFileName);
    }

    /**
     * Gets the URL of the model's thumbnail image.
     *
     * @return string
     */
    public function getPlotImageUrlAttribute()
    {
        if (!$this->plot_extension) return "/images/stage0.png";
        return asset($this->imageDirectory . '/' . $this->plotImageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('cultivation?name='.$this->name);
    }

    /**
     * Gets the URL of the individual item's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute()
    {
        return url('cultivation/'.$this->id);
    }


    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

}
