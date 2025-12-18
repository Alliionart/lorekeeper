<?php

namespace App\Models\Background;

use App\Models\Character\Character;
use App\Models\Model;
use App\Models\WorldExpansion\Location;

class Background extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'image_id', 'is_visible',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'backgrounds';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'                => 'required|between:3,100',
        'is_visible'          => 'nullable',
        'image_id'            => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'                => 'required|between:3,100',
        'is_visible'          => 'nullable',
        'image_id'            => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    public function conditions() {
        return $this->hasMany(BackgroundCondition::class, 'background_id', 'id');
    }

    public function groupedConditions() {
        return $this->conditions()->get()->groupBy('type')
            ->map(function ($group) {
                return $group->pluck('value')->all();
            })
            ->toArray();
    }

    public function getConditionTypeListAttribute() {
        $list = $this->conditions()->whereNotNull('value')->pluck('type')->unique()->values()->toArray();

        return count($list) > 0 ? implode(', ', $list) : 'Free to use';
    }

    public function location() {
        return Location::find($this->conditions()->pluck('location')->unique()->values()->first());
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort bases in alphabetical order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false) {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort bases by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort bases oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to show only visible bases.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('edit_data')) {
            return $query;
        }

        return $query->where('is_visible', 1);
    }

    /**
     * Scope a query to show all unique values of a specific column.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $column_type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUniqueColumn($query, $column_type) {
        return BackgroundCondition::select('value')
            ->where('type', $column_type)
            ->distinct()
            ->pluck('value');
    }

    /**
     * Scope a query to show only visible bases.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $key
     * @param mixed|null                            $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCondition($query, $key, $value) {
        return $query->whereHas('conditions', function ($q) use ($key, $value) {
            $q->where($key, $value);
        });
    }

    /**
     * Scope a query to show only visible bases.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplicableCharacterBackgrounds($query, Character $character) {
        $background_items = $this->scopeUniqueColumn($query, 'Item');

        return $query->whereHas('conditions', function ($q) use ($character) {
            $q->where(function ($sub) use ($character) {
                $sub->where('user_id', $character->user_id)
                    ->orWhere('location', $character->location)
                    ->orWhere('status', $character->status);
                // ->orWhere('award_id', $character->award_id);
            });
        });
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/backgrounds';
    }

    /**
     * Gets the key for the base.
     *
     * @return string
     */
    public function getBaseKeyName() {
        return str_replace(' ', '_', strtolower($this->name));
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->id.'-background.png';
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
    public function getImageUrlAttribute() {
        if (!$this->id) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the file name of the model's thumbnail image.
     *
     * @return string
     */
    public function getThumbnailFileNameAttribute() {
        return $this->id.'-thumbnaiil-bg.png';
    }

    /**
     * Gets the path to the file directory containing the model's thumbnail image.
     *
     * @return string
     */
    public function getThumbnailPathAttribute() {
        return $this->imagePath;
    }

    /**
     * Gets the URL of the model's thumbnail image.
     *
     * @return string
     */
    public function getThumbnailUrlAttribute() {
        return asset($this->imageDirectory.'/'.$this->thumbnailFileName);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/background/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }
}
