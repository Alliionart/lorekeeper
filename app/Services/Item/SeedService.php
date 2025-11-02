<?php namespace App\Services\Item;

use App\Services\Service;

use DB;
use Carbon\Carbon;

use App\Models\Item\Item;
use App\Models\Currency\Currency;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;

class SeedService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Seed Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of Seed type items.
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
            'items' => Item::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables' => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles' => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
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
        $data = [];
        $rewards = [];
        if($tag->data) {
            $assets = parseAssetData($tag->data);
            foreach($assets as $type => $a)
            {
                $class = getAssetModelString($type, false);
                foreach($a as $id => $asset)
                {
                    $rewards[] = (object)[
                        'rewardable_type' => $class,
                        'rewardable_id' => $id,
                        'quantity' => $asset['quantity']
                    ];
                }
            }
            $data['rewards'] = $rewards;
            $data['stage_2_days'] = $tag->data['stage_2_days'];
            $data['stage_3_days'] = $tag->data['stage_3_days'];
            $data['stage_4_days'] = $tag->data['stage_4_days'];
            $data['stage_5_image'] = $tag->data['stage_5_image'] ?? null;

        }


        return $data;
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

        // If there's no data, return.
        if(!isset($data['rewardable_type'])) throw new \Exception("Please set at least one reward.");
        if(isset($data['stage_2_days']) && $data['stage_2_days'] <= 0) throw new \Exception("Stage 2 days must be greater than 0.");
        if(isset($data['stage_3_days']) && $data['stage_3_days'] <= 0) throw new \Exception("Stage 3 days must be greater than 0.");
        if(isset($data['stage_4_days']) && $data['stage_4_days'] <= 0) throw new \Exception("Stage 4 days must be greater than 0.");


        DB::beginTransaction();

        try {
        

            // The data will be stored as an asset table, json_encode()d. 
            // First build the asset table, then prepare it for storage.
            $assets = createAssetsArray();
            foreach($data['rewardable_type'] as $key => $r) {
                switch ($r)
                {
                    case 'Item':
                        $type = 'App\Models\Item\Item';
                        break;
                    case 'Currency':
                        $type = 'App\Models\Currency\Currency';
                        break;
                    case 'LootTable':
                        $type = 'App\Models\Loot\LootTable';
                        break;
                    case 'Raffle':
                        $type = 'App\Models\Raffle\Raffle';
                        break;
                }
                $asset = $type::find($data['rewardable_id'][$key]);
                addAsset($assets, $asset, $data['quantity'][$key]);
            }
            $assets = getDataReadyAssets($assets);

            if(isset($data['stage_2_days'])) $assets['stage_2_days'] = $data['stage_2_days'];
            if(isset($data['stage_3_days'])) $assets['stage_3_days'] = $data['stage_3_days'];
            if(isset($data['stage_4_days'])) $assets['stage_4_days'] = $data['stage_4_days'];

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);

                $saveName = $tag->id.'-image.'. $image->getClientOriginalExtension();
                $fileName = $tag->id.'-image.'. $image->getClientOriginalExtension().'?v='. Carbon::now()->format('mdY_').randomString(6);

                $assets['stage_5_image'] = 'images/data/items/seeds/'.$fileName;
            }

            if($image) $this->handleImage($image, public_path('images/data/items/seeds/'), $saveName);
            else $assets['stage_5_image'] = ( $tag->getData() ? $tag->getData()['stage_5_image'] : null);

            $tag->update(['data' => json_encode($assets)]);

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
        // not needed as seed is planted not used from inventory
    }

}