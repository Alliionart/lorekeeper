<?php

namespace App\Models\Queue;

use App\Models\Guild\Guild;
use App\Models\Model;

class QueueSubmissionGuild extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'queue_submission_id', 'guild_id', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'queue_submission_guilds';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the submission this is attached to.
     */
    public function submission() {
        return $this->belongsTo(QueueSubmission::class, 'queue_submission_id');
    }

    /**
     * Get the guild being attached to the submission.
     */
    public function guild() {
        return $this->belongsTo(Guild::class, 'guild_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/
}
