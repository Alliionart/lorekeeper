<?php

namespace App\Models\Breeding;

use App\Models\Model;

class Breeding extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'staff_id', 'status', 'character_data', 'breeding_data', 'results'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'breeding';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'user_id'             => 'required',
        'status'              => 'default:pending',
        'character_data'      => 'nullable',
        'breeding_data'       => 'nullable',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'user_id'             => 'required',
        'staff_id'            => 'required',
        'status'              => 'default:pending',
        'character_data'      => 'nullable',
        'breeding_data'       => 'nullable',
        'results'             => 'nullable',
    ];

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort breeding entries in alphabetical order.
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
     * Scope a query to sort breeding entries by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort breeding entries oldest first.
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
        return '<a href="/breeding/'.$this->id.'">Breeding (#'.$this->id.')</a>';
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
