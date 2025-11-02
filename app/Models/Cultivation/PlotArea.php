<?php

namespace App\Models\Cultivation;

use Illuminate\Database\Eloquent\Model;
use DB;

class PlotArea extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plot_id', 'area_id'
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'plot_area';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the plots.
     */
    public function attachedPlot()
    {
        return $this->belongsTo('App\Models\Cultivation\CultivationPlot', 'plot_id');
    }


    /**
     * Get the areas.
     */
    public function attachedArea()
    {
        return $this->belongsTo('App\Models\Cultivation\CultivationArea', 'area_id');
    }



    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/


}
