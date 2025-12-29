<?php
namespace App\Services\Queue;

use App\Models\User\User;
use App\Services\Service;
use Auth;
use DB;
use Intervention\Image\Facades\Image;

class GuildsService extends Service
{

    /**
     * Retrieves any data that should be used in the queue type editing form on the admin side
     *
     * @return array
     */
    public function getEditData()
    {

        return [

        ];
    }

    /**
     * Retrieves any data that should be used in the queue type on the user side
     *
     * @return array
     */
    public function getActData($queue)
    {
        return [
        ];
    }

    /**
     * Processes the data attribute of the queue and returns it in the preferred format.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getData($data)
    {
        return $data;
    }

    /**
     * Processes the data attribute of the queue and returns it in the preferred format.
     *
     * @param  object  $queue
     * @param  array   $data
     * @return bool
     */
    public function updateData($queue, $data)
    {

        return [
        ];
    }

    /**
     * Handle any validation on-submit to the queue.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function submit($queue, $data, $user, $submission)
    {
        DB::beginTransaction();

        try {

            //any data handled here should only be that which is required by this particular queue type, as the rest is already handled by the queue service itself

            //let's start by validating the input we have from the user :tm:

            
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Delete the associated data that is custom to this queue.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function delete($queue, $data, $user, $submission)
    {
        DB::beginTransaction();

        try {
            //handle any custom delete functions

            
            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Delete the associated data that is custom to this queue.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function approve($queue, $data, $user, $submission)
    {
        DB::beginTransaction();

        try {
           

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}