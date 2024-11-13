<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;
use Settings;

use App\Models\Theme;
use App\Models\User\User;
use App\Models\User\UserArea;
use App\Models\User\UserItem;
use App\Models\User\UserPlot;

use Carbon\Carbon;

class CultivationManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Cultivation Manager
    |--------------------------------------------------------------------------
    |
    | Handles crediting areas, plots, and the actions taken on them.
    |
    */

   

    /**
     * Credits area to a user.
     *
     * @param  \App\Models\User\User                        $recipient
     * @param  \App\Models\Cultivation\CultivationArea      $area
     * @return  bool
     */
    public function unlockArea($recipient, $area) {
        DB::beginTransaction();

        try {
            if ($recipient->areas->contains($area)) {
                flash("You already unlocked the area " . $area->name ."!", 'warning');
                return $this->commitReturn(false);
            }

            UserArea::create(['user_id' => $recipient->id, 'area_id' => $area->id]);
            
            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Prepares a plot for usage using a tool item.
     */
    public function preparePlot($userToolId, $plotNumber, $areaId){
        DB::beginTransaction();

        try {
            $userTool = UserItem::find($userToolId);
            if(!isset($userTool)) throw new \Exception("Tool could not be found.");
            $userArea = UserArea::find($areaId);
            if(!isset($userArea)) throw new \Exception("Area could not be found.");

            $tag = $userTool->item->tag('tool');
            if(!isset($tag) || !isset($tag->data['plot_id'])) throw new \Exception("Tool is missing its item tag information.");

            if(!$userArea->area->allowedPlots->pluck('id')->contains((int)$tag->data['plot_id'])) throw new \Exception("This tool cannot be used for this area.");

            $userPlot = UserPlot::where('user_id', $userTool->user_id)->where('plot_number', $plotNumber)->where('user_area_id', $areaId)->first();


            if(isset($userPlot)){
                //update plot with the new type and reset stage
                $userPlot->update([
                    'plot_id' => $tag->data['plot_id'],
                    'stage' => 1,
                    'counter' => 0,
                ]);
            } else {
                //create plot
                //'user_id', 'plot_id', 'item_id', 'user_area_id', 'stage', 'tended_at', 'plot_number'
                $userPlot = UserPlot::create([
                    'user_id' => $userTool->user_id,
                    'plot_id' => $tag->data['plot_id'],
                    'user_area_id' => $areaId,
                    'stage' => 1,
                    'counter' => 0,
                    'plot_number' => $plotNumber
                ]);
            }

            // debit tool item
            $invman = new InventoryManager;
            if(!$invman->debitStack($userTool->user, 'Used to prepare a plot.', ['data' => 'Used to prepare a plot on area '.$userArea->area->name.'.'], $userTool, 1)) {
                throw new \Exception("Could not debit item.");
            }
            return $this->commitReturn(true);

        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);

    }


    /**
     * Plants an item in the plot.
     */
    public function cultivatePlot($userSeedId, $plotNumber, $areaId){
        DB::beginTransaction();

        try {
            $userSeed = UserItem::find($userSeedId);
            if(!isset($userSeed)) throw new \Exception("Seed could not be found.");
            $userArea = UserArea::find($areaId);
            if(!isset($userArea)) throw new \Exception("Area could not be found.");

            $tag = $userSeed->item->tag('seed');
            if(!isset($tag)) throw new \Exception("Seed is missing its item tag.");


            $userPlot = UserPlot::where('user_id', $userSeed->user_id)->where('plot_number', $plotNumber)->where('user_area_id', $areaId)->first();

            if(isset($userPlot)){
                if(!$userPlot->plot->allowedItems->pluck('id')->contains($userSeed->item->id)) throw new \Exception("This item cannot be cultivated on this plot.");

                //update plot with the new type and set stage
                $userPlot->update([
                    'item_id' => $userSeed->item->id,
                    'stage' => 2,
                ]);
            } else {
                throw new \Exception("User plot was not found.");
            }

            // debit seed item
            $invman = new InventoryManager;
            if(!$invman->debitStack($userSeed->user, 'Used to cultivate a plot.', ['data' => 'Used to cultivate a plot on area '.$userArea->area->name.'.'], $userSeed, 1)) {
                throw new \Exception("Could not debit item.");
            }
            return $this->commitReturn(true);

        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);

    }

    /**
     * Tends to a plot.
     */
    public function tendPlot($plotId){
        DB::beginTransaction();

        try {
            $userPlot = UserPlot::find($plotId);
            if(!isset($userPlot)) throw new \Exception("Plot could not be found.");
            $date = date("Y-m-d H:i:s", strtotime('midnight'));

            if($this->canTend($userPlot->tended_at, $date)){
                $caredPlots = UserPlot::where('user_id', $userPlot->user_id)->where('tended_at', '>=', $date)->get();
                if(Settings::get('cultivation_care_cooldown') > 0 && $caredPlots->count() >= Settings::get('cultivation_care_cooldown')) throw new \Exception("You already tended to ". $caredPlots->count()." plot(s) today.");
                $newStage = ($userPlot->counter + 1 >= $userPlot->getStageProgress() && $userPlot->stage < 5) ? $userPlot->stage + 1 : $userPlot->stage;
                $newCount = ($newStage > $userPlot->stage) ? 0 : $userPlot->counter + 1;
    
                $userPlot->update([
                        'counter' => $newCount,
                        'stage' => $newStage,
                        'tended_at' => Carbon::now()
                ]);
            } else {
                throw new \Exception("This plot has already been tended to for the day.");
            }

            return $this->commitReturn(true);

        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);

    }

    private function canTend($tendedAt, $date)
    {
        if($tendedAt){
            if($tendedAt >= $date) return false;
        }
        return true;
    }

    /**
     * Harvest a plot.
     */
    public function harvestPlot($plotId, $user){
        DB::beginTransaction();

        try {
            $userPlot = UserPlot::find($plotId);
            if(!isset($userPlot)) throw new \Exception("Plot could not be found.");

            if($userPlot->stage == 5){
                //dd($userPlot->counter, $userPlot->getStageProgress());
                $seedTag = $userPlot->item->tag('seed');
                if(!isset($seedTag)) throw new \Exception("Seed tag data could not be found.");
                // Distribute user rewards
                if(!$rewards = fillUserAssets(parseAssetData($seedTag->data), $user, $user, 'Harvesting Rewards', [
                                            'data' => 'Received from harvesting a '.$userPlot->plot->name.' plot.'
                ])) throw new \Exception("Failed to harvest plot.");


                //reset plot
                if(Settings::get('cultivation_plot_usability') == 0){
                    $userPlot->update([
                        'counter' => 0,
                        'stage' => 1,
                        'item_id' => null,
                    ]);
                } else {
                    $userPlot->delete();
                }

                $this->commitReturn(true);
                return $rewards;
            } else {
                throw new \Exception("This plot is not ready for harvest yet.");
            } 

        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        $this->rollbackReturn(false);
        return null;

    }


    /**
     * Deletes a user area.
     *
     * @param  \App\Models\Cultivation\UserArea  $area
     * @return bool
     */
    public function deleteArea($area)
    {
        DB::beginTransaction();

        try {
            $area->plots()->delete();
            $area->delete();
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
