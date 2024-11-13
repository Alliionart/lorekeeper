<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\Cultivation\CultivationArea;
use App\Models\Cultivation\CultivationPlot;
use App\Models\Cultivation\PlotItem;
use App\Models\Cultivation\PlotArea;
use App\Models\User\UserArea;
use App\Models\User\UserPlot;

class CultivationService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Cultivation Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of areas and plots.
    |
    */

    /**********************************************************************************************
     
        AREA

    **********************************************************************************************/
    
    /**
     * Creates a new area.
     *
     * @param  array                  $data 
     * @return bool|\App\Models\Cultivation\CultivationArea
     */
    public function createArea($data)
    {
        DB::beginTransaction();

        try {

            if(CultivationArea::where('name', $data['name'])->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateAreaData($data);

            $bgImage = null;
            if(isset($data['background_image']) && $data['background_image']) {
                $bgImage = $data['background_image'];
                unset($data['background_image']);
            }
            $plotImage = null;
            if(isset($data['plot_image']) && $data['plot_image']) {
                $plotImage = $data['plot_image'];
                unset($data['plot_image']);
            }

            $created = CultivationArea::create($data);
            if ($bgImage) {
                $created->background_extension = $bgImage->getClientOriginalExtension();
                $this->handleImage($bgImage, $created->imagePath, $created->backgroundImageFileName, null);
            }
            if ($plotImage) {
                $created->plot_extension = $plotImage->getClientOriginalExtension();
                $this->handleImage($plotImage, $created->imagePath, $created->plotImageFileName, null);
            }

            
            //add plots
            if(isset($data["plot_id"])){
                foreach($data["plot_id"] as $plot_id){
                    PlotArea::create(['area_id' => $created->id, 'plot_id' => $plot_id]);
                }
            }

            $created->update();

            return $this->commitReturn($created);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**
     * Updates a area.
     *
     * @param  \App\Models\Cultivation\CultivationArea  $area
     * @param  array                  $data 
     * @return bool|\App\Models\Cultivation\CultivationArea
     */
    public function updateArea($area, $data)
    {
            // More specific validation
            if(CultivationArea::where('name', $data['name'])->where('id', '!=', $area->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateAreaData($data, $area);

            $bgImage = null;
            if(isset($data['background_image']) && $data['background_image']) {
                if(isset($area->background_extension)) $old = $area->backgroundImageFileName;
                else $old = null;
                $bgImage = $data['background_image'];
                unset($data['background_image']);
            }
            if ($bgImage) {
                $area->background_extension = $bgImage->getClientOriginalExtension();
                $this->handleImage($bgImage, $area->imagePath, $area->backgroundImageFileName, $old);
            }

            $plotImage = null;
            if(isset($data['plot_image']) && $data['plot_image']) {
                if(isset($area->thumb_extension)) $old = $area->thumbFileName;
                else $old = null;
                $plotImage = $data['plot_image'];
                unset($data['plot_image']);
            }

            if ($plotImage) {
                $area->plot_extension = $plotImage->getClientOriginalExtension();
                $this->handleImage($plotImage, $area->imagePath, $area->plotImageFileName, $old);
            }


            //update plots
            //remove old plots
            if(!isset($data["plot_id"])) $area->plotAreas()->delete();
            if(isset($data["plot_id"])){
                //add set plots if not yet there
                foreach(array_filter($data["plot_id"]) as $plot_id){
                    if($area->plotAreas->where('plot_id', $plot_id)->where('area_id', $area->id)->count() <= 0) PlotArea::create(['area_id' => $area->id, 'plot_id' => $plot_id]);
                }
            }

            $area->update($data);

            return $this->commitReturn($area);
    }
    
    /**
     * Processes user input for creating/updating a area.
     *
     * @param  array                  $data 
     * @param  \App\Models\Cultivation\CultivationArea  $area
     * @return array
     */
    private function populateAreaData($data, $area = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        $data['is_active'] = isset($data['is_active']);
        
        if(isset($data['remove_background']))
        {
            if($area && isset($area->background_extension) && $data['remove_background'])
            {
                $data['background_extension'] = null;
                $this->deleteImage($area->imagePath, $area->backgroundImageFileName);
            }
            unset($data['remove_background']);
        }
        if(isset($data['remove_plot']))
        {
            if($area && isset($area->plot_extension) && $data['remove_plot'])
            {
                $data['plot_extension'] = null;
                $this->deleteImage($area->imagePath, $area->plotImageFileName);
            }
            unset($data['remove_plot']);
        }

        return $data;
    }
    
    /**
     * Deletes a area.
     *
     * @param  \App\Models\Cultivation\CultivationArea  $area
     * @return bool
     */
    public function deleteArea($area)
    {
        DB::beginTransaction();

        try {

            if(UserArea::where('area_id', $area->id)->exists()) throw new \Exception("This area is unlocked by a user and cannot be deleted.");

            if(isset($area->background_extension)) $this->deleteImage($area->imagePath, $area->backgroundImageFileName);
            if(isset($area->plot_extension)) $this->deleteImage($area->imagePath, $area->plotImageFileName);
            
            $area->plotAreas()->delete();
            $area->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts area order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortAreas($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                CultivationArea::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**********************************************************************************************
     
        PLOT

    **********************************************************************************************/
    
    /**
     * Creates a new area.
     *
     * @param  array                  $data 
     * @return bool|\App\Models\Cultivation\CultivationPlot
     */
    public function createPlot($data)
    {
        DB::beginTransaction();

        try {

            if(CultivationPlot::where('name', $data['name'])->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populatePlotData($data);
            
            $stage1Image = null;
            if(isset($data['stage_1_image']) && $data['stage_1_image']) {
                $stage1Image = $data['stage_1_image'];
                unset($data['stage_1_image']);
            }
            $stage2Image = null;
            if(isset($data['stage_2_image']) && $data['stage_2_image']) {
                $stage2Image = $data['stage_2_image'];
                unset($data['stage_2_image']);
            }
            $stage3Image = null;
            if(isset($data['stage_3_image']) && $data['stage_3_image']) {
                $stage3Image = $data['stage_3_image'];
                unset($data['stage_3_image']);
            }
            $stage4Image = null;
            if(isset($data['stage_4_image']) && $data['stage_4_image']) {
                $stage4Image = $data['stage_4_image'];
                unset($data['stage_4_image']);
            }
            $stage5Image = null;
            if(isset($data['stage_5_image']) && $data['stage_5_image']) {
                $stage5Image = $data['stage_5_image'];
                unset($data['stage_5_image']);
            }

            $created = CultivationPlot::create($data);

            if ($stage1Image) {
                $created->stage_1_extension = $stage1Image->getClientOriginalExtension();
                $this->handleImage($stage1Image, $created->imagePath, $created->getStageImageFileNameAttribute(1), null);
            }
            
            if ($stage2Image) {
                $created->stage_2_extension = $stage2Image->getClientOriginalExtension();
                $this->handleImage($stage2Image, $created->imagePath, $created->getStageImageFileNameAttribute(2), null);
            }

            
            if ($stage3Image) {
                $created->stage_3_extension = $stage3Image->getClientOriginalExtension();
                $this->handleImage($stage3Image, $created->imagePath, $created->getStageImageFileNameAttribute(3), null);
            }

            
            if ($stage4Image) {
                $created->stage_4_extension = $stage4Image->getClientOriginalExtension();
                $this->handleImage($stage4Image, $created->imagePath, $created->getStageImageFileNameAttribute(4), null);
            }

            
            if ($stage5Image) {
                $created->stage_5_extension = $stage5Image->getClientOriginalExtension();
                $this->handleImage($stage5Image, $created->imagePath, $created->getStageImageFileNameAttribute(5), null);
            }

            $created->update();

            return $this->commitReturn($created);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


        
    /**
     * Updates a plot.
     *
     * @param  \App\Models\Cultivation\CultivationPlot  $plot
     * @param  array                  $data 
     * @return bool|\App\Models\Cultivation\CultivationPlot
     */
    public function updatePlot($plot, $data)
    {
            // More specific validation
            if(CultivationPlot::where('name', $data['name'])->where('id', '!=', $plot->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populatePlotData($data, $plot);

            $stage1Image = null;
            if(isset($data['stage_1_image']) && $data['stage_1_image']) {
                if(isset($plot->stage_1_extension)) $old = $plot->getStageImageFileNameAttribute(1);
                else $old = null;
                $stage1Image = $data['stage_1_image'];
                unset($data['stage_1_image']);
            }
            if ($stage1Image) {
                $plot->stage_1_extension = $stage1Image->getClientOriginalExtension();
                $this->handleImage($stage1Image, $plot->imagePath, $plot->getStageImageFileNameAttribute(1), $old);
            }

            $stage2Image = null;
            if(isset($data['stage_2_image']) && $data['stage_2_image']) {
                if(isset($plot->stage_2_extension)) $old = $plot->getStageImageFileNameAttribute(2);
                else $old = null;
                $stage2Image = $data['stage_2_image'];
                unset($data['stage_2_image']);
            }
            if ($stage2Image) {
                $plot->stage_2_extension = $stage2Image->getClientOriginalExtension();
                $this->handleImage($stage2Image, $plot->imagePath, $plot->getStageImageFileNameAttribute(2), $old);
            }

            $stage3Image = null;
            if(isset($data['stage_3_image']) && $data['stage_3_image']) {
                if(isset($plot->stage_3_extension)) $old = $plot->getStageImageFileNameAttribute(3);
                else $old = null;
                $stage3Image = $data['stage_3_image'];
                unset($data['stage_3_image']);
            }
            if ($stage3Image) {
                $plot->stage_3_extension = $stage3Image->getClientOriginalExtension();
                $this->handleImage($stage3Image, $plot->imagePath, $plot->getStageImageFileNameAttribute(3), $old);
            }

            $stage4Image = null;
            if(isset($data['stage_4_image']) && $data['stage_4_image']) {
                if(isset($plot->stage_4_extension)) $old = $plot->getStageImageFileNameAttribute(4);
                else $old = null;
                $stage4Image = $data['stage_4_image'];
                unset($data['stage_4_image']);
            }
            if ($stage4Image) {
                $plot->stage_4_extension = $stage4Image->getClientOriginalExtension();
                $this->handleImage($stage4Image, $plot->imagePath, $plot->getStageImageFileNameAttribute(4), $old);
            }

            $stage5Image = null;
            if(isset($data['stage_5_image']) && $data['stage_5_image']) {
                if(isset($plot->stage_5_extension)) $old = $plot->getStageImageFileNameAttribute(5);
                else $old = null;
                $stage5Image = $data['stage_5_image'];
                unset($data['stage_5_image']);
            }
            if ($stage5Image) {
                $plot->stage_5_extension = $stage5Image->getClientOriginalExtension();
                $this->handleImage($stage5Image, $plot->imagePath, $plot->getStageImageFileNameAttribute(5), $old);
            }

            
            //update items
            //remove old items
            if(!isset($data["item_id"])) $plot->plotItems()->delete();
            if(isset($data["item_id"])){
                //add set plots if not yet there
                foreach(array_filter($data["item_id"]) as $item_id){
                    if($plot->plotItems->where('item_id', $item_id)->where('plot_id', $plot->id)->count() <= 0) PlotItem::create(['plot_id' => $plot->id, 'item_id' => $item_id]);
                }
            }

            $plot->update($data);

            return $this->commitReturn($plot);
    }

    /**
     * Processes user input for creating/updating a plot.
     *
     * @param  array                  $data 
     * @param  \App\Models\Cultivation\CultivationPlot  $plot
     * @return array
     */
    private function populatePlotData($data, $plot = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        $data['is_active'] = isset($data['is_active']);

        if(isset($data['remove_stage_1']))
        {
            if($plot && isset($plot->stage_1_extension) && $data['remove_stage_1'])
            {
                $data['stage_1_extension'] = null;
                $this->deleteImage($plot->imagePath, $plot->getStageImageFileNameAttribute(1));
            }
            unset($data['remove_stage_1']);
        }

        if(isset($data['remove_stage_2']))
        {
            if($plot && isset($plot->stage_2_extension) && $data['remove_stage_2'])
            {
                $data['stage_2_extension'] = null;
                $this->deleteImage($plot->imagePath, $plot->getStageImageFileNameAttribute(2));
            }
            unset($data['remove_stage_2']);
        }

        if(isset($data['remove_stage_3']))
        {
            if($plot && isset($plot->stage_3_extension) && $data['remove_stage_3'])
            {
                $data['stage_3_extension'] = null;
                $this->deleteImage($plot->imagePath, $plot->getStageImageFileNameAttribute(3));
            }
            unset($data['remove_stage_3']);
        }

        if(isset($data['remove_stage_4']))
        {
            if($plot && isset($plot->stage_4_extension) && $data['remove_stage_4'])
            {
                $data['stage_4_extension'] = null;
                $this->deleteImage($plot->imagePath, $plot->getStageImageFileNameAttribute(4));
            }
            unset($data['remove_stage_4']);
        }

        if(isset($data['remove_stage_5']))
        {
            if($plot && isset($plot->stage_5_extension) && $data['remove_stage_5'])
            {
                $data['stage_5_extension'] = null;
                $this->deleteImage($plot->imagePath, $plot->getStageImageFileNameAttribute(5));
            }
            unset($data['remove_stage_5']);
        }
       

        return $data;
    }

    /**
     * Deletes a plot.
     *
     * @param  \App\Models\Cultivation\CultivationPlot  $plot
     * @return bool
     */
    public function deletePlot($plot)
    {
        DB::beginTransaction();

        try {

            if(UserPlot::where('plot_id', $plot->id)->exists()) throw new \Exception("This plot is in use by a user and cannot be deleted.");

            if(isset($plot->stage_1_extension)) $this->deleteImage($plot->imagePath, $plot->getStageImageFileNameAttribute(1));
            if(isset($plot->stage_2_extension)) $this->deleteImage($plot->imagePath, $plot->getStageImageFileNameAttribute(2));
            if(isset($plot->stage_3_extension)) $this->deleteImage($plot->imagePath, $plot->getStageImageFileNameAttribute(3));
            if(isset($plot->stage_4_extension)) $this->deleteImage($plot->imagePath, $plot->getStageImageFileNameAttribute(4));
            if(isset($plot->stage_5_extension)) $this->deleteImage($plot->imagePath, $plot->getStageImageFileNameAttribute(5));

            $plot->plotItems()->delete();
            $plot->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts plot order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortPlot($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                CultivationPlot::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}