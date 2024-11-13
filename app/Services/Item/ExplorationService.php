<?php namespace App\Services\Item;

use App\Services\Service;

use DB;
use Settings;
use App\Models\Cultivation\CultivationArea;
use App\Services\InventoryManager;

class ExplorationService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Exploration Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of Exploration type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData()
    {
        return [
             'areas' => CultivationArea::orderBy('sort')->pluck('name', 'id'),
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getTagData($tag)
    {
        return $tag->data;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param  string  $tag
     * @param  array   $data
     * @return bool
     */
    public function updateData($tag, $data)
    {
        $exData = [];
        //put inputs into an array to transfer to the DB
        if(isset($data['area_id'])) $exData['area_id'] = $data['area_id'];

        DB::beginTransaction();
        
        try {
            //get pairingData array and put it into the 'data' column of the DB for this tag
            $tag->update(['data' => json_encode($exData)]);
        
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Acts upon the item when used from the inventory.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function act($stacks, $user, $data)
    {
        DB::beginTransaction();

        try {

            if(Settings::get('cultivation_area_unlock') > 0 && $user->areas->count() >= Settings::get('cultivation_area_unlock')) 
                throw new \Exception("You can only cultivate in ".Settings::get('cultivation_area_unlock')." area(s) at once. To unlock a new area, please abandon one of your current ones first.");

            
            $stack = $stacks->first();
            if(count($data['quantities']) > 1 || $data['quantities'][0] > 1) throw new \Exception("You can only unlock an area once. Please use only one item to do so!");

            $firstData = $stack->item->tag('exploration')->data;
            if(!isset($firstData['area_id'])) throw new \Exception("No area set for this tag.");
            $area = CultivationArea::find($firstData['area_id']);
            if(!isset($area)) throw new \Exception("The area to unlock could not be found.");

            $assets['areas'] = [$area->id => 1];
              
            // check user owns this item
            if ($stack->user_id != $user->id) throw new \Exception("This item does not belong to you.");
        
            // debit item and distribute area
            if ((new InventoryManager)->debitStack($stack->user, 'Area Unlocked', ['data' => ''], $stack, 1)) {

            if (!$rewards = fillUserAssets(parseAssetData($assets), $user, $user, 'Area Unlocked', [
                'data' => 'Unlocked via ' . $stack->item->name
            ])) throw new \Exception("Failed to use exploration item.");
                flash("You have unlocked the following area: ".$area->name);
            }
            
          
          return $this->commitReturn(true);
        } catch (\Exception $e) {
          $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}