<?php

namespace App\Models\Breeding;

use App\Models\Model;
use App\Models\User\User;

class BreedingLog extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id',
        'log', 'log_type', 'data',
        'breeding_id', 'parent_1_id', 'parent_2_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'breeding_log';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who initiated the logged action.
     */
    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the breeding that is the target of the action.
     */
    public function breeding() {
        return $this->belongsTo(Breeding::class);
    }
}
