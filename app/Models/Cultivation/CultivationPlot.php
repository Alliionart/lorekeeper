<?php

namespace App\Models\Cultivation;

use Config;
use DB;
use App\Models\Model;


class CultivationPlot extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'parsed_description', 'is_active',
        'stage_1_extension', 'stage_2_extension', 'stage_3_extension', 'stage_4_extension', 'stage_5_extension'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cultivation_plot';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|unique:items|between:3,100',
        'description' => 'nullable',
        'stage_1_image' => 'mimes:png,jpg',
        'stage_2_image' => 'mimes:png,jpg',
        'stage_3_image' => 'mimes:png,jpg',
        'stage_4_image' => 'mimes:png,jpg',
        'stage_5_image' => 'mimes:png,jpg',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required|unique:items|between:3,100',
        'description' => 'nullable',
        'stage_1_image' => 'mimes:png,jpg',
        'stage_2_image' => 'mimes:png,jpg',
        'stage_3_image' => 'mimes:png,jpg',
        'stage_4_image' => 'mimes:png,jpg',
        'stage_5_image' => 'mimes:png,jpg',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get areas that use this plot.
     */
    public function areas()
    {
        return $this->belongsToMany('App\Models\Cultivation\CultivationArea', 'plot_area', 'plot_id', 'area_id');
    }

    /**
     * Get the items that can be planted on this plot.
     */
    public function allowedItems()
    {
        return $this->belongsToMany('App\Models\Item\Item', 'plot_item', 'plot_id', 'item_id');
    }

    /**
     * Get the plot items.
     */
    public function plotItems()
    {
        return $this->hasMany('App\Models\Cultivation\PlotItem', 'plot_id');
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
        return 'images/data/plots';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getStageImageFileNameAttribute($stage)
    {
        return $this->id . '-stage-'. $stage .'.png';
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
     * Gets the URL of the model's current stage image.
     *
     * @return string
     */
    public function getStageImage($stage)
    {
        //stage 1 = nothing planted 
        if (!$this->stage_1_extension && $stage == 1) return "/images/stage1.png";
        if (!$this->stage_2_extension && $stage == 2) return "/images/stage2.png";
        if (!$this->stage_3_extension && $stage == 3) return "/images/stage3.png";
        if (!$this->stage_4_extension && $stage == 4) return "/images/stage4.png";
        if (!$this->stage_5_extension && $stage == 5) return "/images/stage5.png";

        return asset($this->imageDirectory . '/' . $this->getStageImageFileNameAttribute($stage));
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('world/cultivation/plots?name='.$this->name);
    }

    /**
     * Gets the URL of the individual item's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute()
    {
        return url('world/cultivation/plots/'.$this->id);
    }


    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

}
