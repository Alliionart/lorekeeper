<?php

namespace App\Models\Claymore;

use App\Models\Model;

class Ability extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_id', 'name', 'description', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'abilities';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'type_id'          => 'nullable',
        'name'             => 'required|unique:abilities|between:3,100',
        'description'      => 'nullable',
        'data'             => 'nullable',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'type_id'          => 'nullable',
        'name'             => 'required|unique:abilities|between:3,100',
        'description'      => 'nullable',
        'data'             => 'nullable',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the type(s) the ability belongs to.
     */
    public function type() {
        return $this->belongsTo(AbilityType::class, 'type_id');
    }

    /**
     * Get the stats of the ability.
     */
    public function stats() {
        return $this->hasMany(AbilityStat::class);
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort gears in alphabetical order.
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
     * Scope a query to sort gears in category order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortCategory($query) {
        if (AbilityType::all()->count()) {
            return $query->orderBy(AbilityType::select('sort')->whereColumn('type_id', 'ability_types.id'), 'DESC');
        }

        return $query;
    }

    /**
     * Scope a query to sort gears by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort features oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-item">'.$this->name.'</a>';
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/abilties?name='.$this->name);
    }

    /**
     * Gets the URL of the individual gear's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute() {
        return url('world/abilties/'.$this->id);
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'abilties';
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/abilties/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_claymores';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * displays the gear's name, with stats.
     */
    public function displayWithStats() {
        $stats = $this->stats->sortByDesc('value')->map(function ($stat) {
            return $stat->stat->name.' + '.$stat->count;
        })->implode(', ');

        return $this->name.'<br />'.($stats ? ' ('.$stats.')' : '');
    }
}
