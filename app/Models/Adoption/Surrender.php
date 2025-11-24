<?php

namespace App\Models\Adoption;

use Config;
use Auth;
use App\Models\Model;
use App\Models\Feature\FeatureCategory;
use App\Models\Marking\Marking;
use App\Models\Character\CharacterMarking;
use App\Models\Character\Character;
use App\Models\Rarity;
use App\Models\Feature\Feature;
use App\Models\Character\CharacterCategory;

class Surrender extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'user_id', 'staff_id', 'notes',
        'comments', 'staff_comments',
        'status', 'worth', 'currency_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'surrenders';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;
    
    /**
     * Validation rules for surrender creation.
     *
     * @var array
     */
    public static $createRules = [
        'character_id' => 'required',
    ];
    
    /**
     * Validation rules for surrender updating.
     *
     * @var array
     */
    public static $updateRules = [
        'character_id' => 'required',
    ];

    /**********************************************************************************************
    
        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort surrenders oldest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query)
    {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to sort surrenders by newest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query)
    {
        return $query->orderBy('id', 'DESC');
    }

     /**
     * Scope a query to only include pending surrenders.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope a query to only include viewable surrenders.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeViewable($query, $user)
    {
        if($user && $user->hasPower('manage_surrenders')) return $query;
        return $query->where(function($query) use ($user) {
            if($user) $query->where('user_id', $user->id)->orWhere('status', 'Approved');
            else $query->where('status', 'Approved');
        });
    }

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
    
    /**
     * Get the character this surrender is for.
     */
    public function character() 
    {
        return $this->belongsTo('App\Models\Character\Character', 'character_id');
    }
    
    /**
     * Get the user who made the surrender.
     */
    public function user() 
    {
        return $this->belongsTo('App\Models\User\User', 'user_id');
    }
    
    /**
     * Get the staff who processed the surrender.
     */
    public function staff() 
    {
        return $this->belongsTo('App\Models\User\User', 'staff_id');
    }

    /**********************************************************************************************
    
        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the viewing URL of the surrender/claim.
     *
     * @return string
     */
    public function getViewUrlAttribute()
    {
        return url('surrender/view/'.$this->id);
    }

    /**
     * Get the admin URL (for processing purposes) of the surrender/claim.
     *
     * @return string
     */
    public function getAdminUrlAttribute()
    {
        return url('admin/surrenders/edit/'.$this->id);
    }

    /**********************************************************************************************
    
        CALCULATOR

    **********************************************************************************************/

    /**
     * Gets the estimated worth of a character.
     * 
     * @param int $id The character ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getCharacterValue($id) {
        $character = Character::where('id', $id)->where('user_id', Auth::user()->id)->first();
        if(!$character) {
            return response()->json(['error' => 'Character not found.'], 404);
        }

        $features = $character->image->features()->get();

        $totalcost = 0;
        $breakdown = [];

        //Calucate worth based on species/subtype
        $species = $character->image->species;
        $subtype = $character->image->subtype;
        if ($species && $subtype) {
            switch ($species->name) {
                case 'Vayron':
                    switch($subtype->name) {
                        case 'Puller':
                        case 'Chaser':
                            $cost = 600;
                        break;
                        case 'Runner':
                            $cost = 500;
                        break;
                    }
                break;
                case 'Tyrian':
                    switch($subtype->name) {
                        case 'Empyrian':
                            $cost = 1000;
                        break;
                        case 'Haedian':
                            $cost = 1000;
                        break;
                    }
                break;
            }
            $breakdown['species'][$species->name.' - '.$subtype->name] = $cost ?? 0;
            $totalcost += $cost ?? 0;
        }
        //Calculate worth based on feature rarities
        foreach ($features as $trait) {
            $rarity = Rarity::where('id', $trait->rarity_id)->first();
            $category = FeatureCategory::where('id', $trait->feature_category_id)->first();
            switch ($category->name) {
                case 'Eyes':
                case 'Ears':
                case 'Tails':
                    switch ($rarity->name) {
                        case 'Common':
                            $cost = 100;
                        break;
                        case 'Uncommon':
                            $cost = 300;
                        break;
                        case 'Rare':
                            $cost = 500;
                        break;
                        case 'Very Rare':
                            $cost = 800;
                        break;
                    }
                    $breakdown['traits'][$category->name.' ('.$rarity->name.')'] = $cost;
                    $totalcost += $cost ?? 0;
                break;
                case 'Fur':
                    switch ($rarity->name) {
                        case 'Common':
                            $cost = 0;
                        break;
                        case 'Uncommon':
                            $cost = 300;
                        break;
                        case 'Rare':
                            $cost = 500;
                        break;
                        case 'Very Rare':
                            $cost = 1000;
                        break;
                    }
                    $breakdown['fur'][$category->name.' ('.$trait->name.')'] = $cost;
                    $totalcost += $cost ?? 0;
                break;
                case 'Corrupt Mutation':
                    $cost = 5000;
                    $breakdown['mutation'][$category->name] = $cost;
                    $totalcost += $cost;
                break;
                case 'Magical Mutation':
                    $cost = 10000;
                    $breakdown['mutation'][$category->name] = $cost;
                    $totalcost += $cost;
                break;
            }
        }

        //Calculate worth based on markings
        $cMarkings = CharacterMarking::where('character_id', $id)->get();
        if($cMarkings) {
            foreach($cMarkings as $m) {
                $marking = $m->marking;
                $rarity = Rarity::where('id', $marking->rarity_id)->first();
                switch ($rarity->name) {
                    case 'Common':
                        $cost = 100;
                    break;
                    case 'Uncommon':
                    case 'Modifier':
                        $cost = 500;
                    break;
                    case 'Rare':
                        $cost = 1000;
                    break;
                }
                //Check if it exists, if not create it, if so then add count
                if(!array_key_exists('markings', $breakdown) || !array_key_exists($rarity->name, $breakdown['markings'])) {
                    $breakdown['markings'][$rarity->name] = [
                        'count' => 1,
                        'cost' => $cost
                    ];
                    $totalcost += $cost;
                }
                else {
                    $count = $breakdown['markings'][$rarity->name]['count'];
                    $oldCost = $breakdown['markings'][$rarity->name]['cost'];
                    $breakdown['markings'][$rarity->name]['count'] = $count + 1;
                    $breakdown['markings'][$rarity->name]['cost'] = $oldCost + $cost;
                    $totalcost += $cost;
                }
            }
        }

        //Calculate worth based on skills
        $skills = $character->skills;
        if($skills) {
            foreach($skills as $skill) {
                $rarity = Rarity::where('id', $skill->rarity_id)->first();
                switch ($rarity->name) {
                    case 'Common':
                        $cost = 100;
                    break;
                    case 'Uncommon':
                        $cost = 500;
                    break;
                    case 'Rare':
                        $cost = 1000;
                    break;
                }
                //Check if it exists, if not create it, if so then add count
                if(!array_key_exists('skills', $breakdown) || !array_key_exists($rarity->name, $breakdown['skills'])) {
                    $breakdown['skills'][$rarity->name] = [
                        'count' => 1,
                        'cost' => $cost
                    ];
                    $totalcost += $cost;
                }
                else {
                    $count = $breakdown['skills'][$rarity->name]['count'];
                    $oldCost = $breakdown['skills'][$rarity->name]['cost'];
                    $breakdown['skills'][$rarity->name]['count'] = $count + 1;
                    $breakdown['skills'][$rarity->name]['cost'] = $oldCost + $cost;
                    $totalcost += $cost;
                }
            }
        }

        return response()->json(['estimated_worth' => $totalcost, 'breakdown' => $breakdown]);
    }
    
}
