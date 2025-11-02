<?php namespace App\Services\Item;

use App\Services\Service;

use DB;
use App\Models\Cultivation\CultivationPlot;

class ToolService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Tool Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of Tool type items.
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
             'plots' => CultivationPlot::orderBy('sort')->pluck('name', 'id'),
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
        //put inputs into an array to transfer to the DB
        if(isset($data['plot_id'])) $toolData['plot_id'] = $data['plot_id'];

        DB::beginTransaction();
        
        try {
            //get pairingData array and put it into the 'data' column of the DB for this tag
            $tag->update(['data' => json_encode($toolData)]);
        
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
        // not needed
    }

}