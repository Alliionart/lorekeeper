<?php

namespace App\Models\Guild;

use App\Models\Model;
use App\Models\User\User;

class GuildUpdate extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'guild_id', 'user_id', 'staff_id', 'is_update', 'status', 'name', 'description', 'has_logo', 'staff_comment'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guild_updates';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the guild.
     */
    public function guild() {
        return $this->belongsTo(Guild::class, 'guild_id');
    }

    /**
     * Get the user associated with this application.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the staff member associated with this application.
     */
    public function staff() {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include pending submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope a query to only include drafted submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDrafts($query) {
        return $query->where('status', 'Drafts');
    }

    /**
     * Scope a query to only include viewable submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeViewable($query, $user = null) {
        $forbiddenSubmissions = $this
            ->whereHas('prompt', function ($q) {
                $q->where('hide_submissions', 1)->whereNotNull('end_at')->where('end_at', '>', Carbon::now());
            })
            ->orWhereHas('prompt', function ($q) {
                $q->where('hide_submissions', 2);
            })
            ->orWhere('status', '!=', 'Approved')->pluck('id')->toArray();

        if ($user && $user->hasPower('manage_submissions')) {
            return $query;
        } else {
            return $query->where(function ($query) use ($user, $forbiddenSubmissions) {
                if ($user) {
                    $query->whereNotIn('id', $forbiddenSubmissions)->orWhere('user_id', $user->id);
                } else {
                    $query->whereNotIn('id', $forbiddenSubmissions);
                }
            });
        }
    }

    /**
     * Scope a query to sort submissions oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to sort submissions by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the admin URL (for processing purposes) of the submission/claim.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/guilds/queue/'.$this->id);
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/guilds/temp/'.$this->id;
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getLogoFileNameAttribute() {
        return $this->id.'-logo.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getLogoUrlAttribute() {
        if (!$this->has_logo) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->LogoFileName);
    }

}
