<?php

namespace App\Models\Cultivation;

use Illuminate\Database\Eloquent\Model;
use DB;

class PlotItem extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plot_id', 'item_id'
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
    protected $table = 'plot_item';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the attachers.
     */
    public function plot()
    {
        return $this->belongsTo('App\Models\Cultivation\CultivationPlot', 'plot_id');
    }

    /**
     * Get the attachers.
     */
    public function item()
    {
        return $this->belongsTo('App\Models\Item\Item', 'item_id');
    }



    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/


}
