<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserPlot extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'plot_id', 'item_id', 'user_area_id', 'stage', 'tended_at', 'plot_number', 'counter'
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_plot';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the attachers.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User\User', 'user_id');
    }

    /**
     * Get the attachers.
     */
    public function userArea()
    {
        return $this->belongsTo('App\Models\User\UserArea', 'user_area_id');
    }

    /**
     * Get the planted item.
     */
    public function item()
    {
        return $this->belongsTo('App\Models\Item\Item', 'item_id');
    }

    /**
     * Get the plot.
     */
    public function plot()
    {
        return $this->belongsTo('App\Models\Cultivation\CultivationPlot', 'plot_id');
    }


    /**********************************************************************************************

        FUNCTIONS

    **********************************************************************************************/

    public function getStageProgress(){
        if(isset($this->item)){
            $seedTag = $this->item->tag('seed');
            if(isset($seedTag)){
                switch($this->stage)
                {
                    case 2:
                        return (int) $seedTag->data['stage_2_days'];
                        break;
            
                    case 3:
                        return (int) $seedTag->data['stage_3_days'];
                        break;
            
                    case 4:
                        return (int) $seedTag->data['stage_4_days'];
                        break;
                }
            } 
        }
        return 0;
    }

    /**
     * Get image for the plot.
     */
    public function getStageImage()
    {
        if(isset($this->item)){
            $seedTag = $this->item->tag('seed');
            if($this->stage == 5 && isset($seedTag) && isset($seedTag->data["stage_5_image"])){
                return "/".$seedTag->data["stage_5_image"];
            }
        }
        return $this->plot->getStageImage($this->stage);
    }

}
