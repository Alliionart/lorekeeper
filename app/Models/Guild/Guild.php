<?php

namespace App\Models\Guild;

use App\Models\Model;
use App\Models\User\User;
use App\Models\Character\Character;
use App\Models\Item\Item;
use Carbon\Carbon;

class Guild extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'owner_id', 'status', 'description',
        'parsed_description', 'location', 'reputation',
        'max_users', 'max_characters',
        'open_new_users', 'automatic_app_approval', 'open_inventory', 'open_bank', 'open_inventory', 'open_pets', 'open_armory',
        'has_logo', 'has_banner',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guilds';

    protected $casts = [
        'joined_at' => 'datetime',
    ];
    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**
     * Validation rules for guild creation.
     *
     * @var array
     */
    public static $createRules = [
        'description' => 'nullable',
        'logo'        => 'nullable|image|mimes:png,gif|max:200',
        'banner'      => 'nullable|image|mimes:png,gif|max:800',
    ];

    /**
     * Validation rules for guild updating.
     *
     * @var array
     */
    public static $updateRules = [
        'description' => 'nullable',
        'logo'        => 'nullable|image|mimes:png,gif|max:200',
        'banner'      => 'nullable|image|mimes:png,gif|max:800',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the owner of the guild.
     */
    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the guild inventory.
     */
    public function items() {
        return $this->belongsToMany(Item::class, 'guild_items')->withPivot('count', 'data', 'updated_at', 'id')->whereNull('guild_items.deleted_at');
    }

    /**
     * Get the characters attached to the guild.
     */
    public function characters() {
        return $this->hasMany(GuildCharacter::class, 'guild_id');
    }

    /**
     * Get the members in the guild.
     */
    public function members() {
        return $this->hasMany(GuildMember::class, 'user_id');
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
     * Gets the inventory of the user for selection.
     *
     * @param mixed $user
     *
     * @return array
     */
    public function getInventory($user) {
        return $this->data && isset($this->data['user']['user_items']) ? $this->data['user']['user_items'] : [];
    }

    /**
     * Gets the currencies of the given user for selection.
     *
     * @param \App\Models\User\User $user
     *
     * @return array
     */
    public function getCurrencies($user) {
        return $this->data && isset($this->data['user']) && isset($this->data['user']['currencies']) ? $this->data['user']['currencies'] : [];
    }

    /**
     * Get the viewing URL of the guild.
     *
     * @return string
     */
    public function getViewUrlAttribute() {
        return url(__('guilds.guilds').'/view/'.$this->id);
    }

    /**
     * Get the editing URL of the guild.
     *
     * @return string
     */
    public function getEditUrlAttribute() {
        return url(__('guilds.guilds').'/edit/'.$this->id);
    }

    /**
     * Get the rank editing URL of the guild.
     *
     * @return string
     */
    public function getEditRankUrlAttribute() {
        return url(__('guilds.guilds').'/edit-ranks/'.$this->id);
    }

    /**
     * Get the admin URL (for processing purposes) of the submission/claim.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/guilds/edit/'.$this->id);
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/guilds';
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
     * Gets the file name of the model's banner.
     *
     * @return string
     */
    public function getBannerFileNameAttribute() {
        return $this->id.'-banner.png';
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

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getBannerUrlAttribute() {
        if (!$this->has_banner) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->BannerFileName);
    }
}
