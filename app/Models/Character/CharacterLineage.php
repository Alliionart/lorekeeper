<?php

namespace App\Models\Character;

use Auth;

use App\Models\Model;

use App\Models\Character\Character;

class CharacterLineage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id',
        'sire_id',              'sire_name',
        'sire_sire_id',         'sire_sire_name',
        'sire_sire_sire_id',    'sire_sire_sire_name',
        'sire_sire_dam_id',     'sire_sire_dam_name',
        'sire_dam_id',          'sire_dam_name',
        'sire_dam_sire_id',     'sire_dam_sire_name',
        'sire_dam_dam_id',      'sire_dam_dam_name',
        'dam_id',               'dam_name',
        'dam_sire_id',          'dam_sire_name',
        'dam_sire_sire_id',     'dam_sire_sire_name',
        'dam_sire_dam_id',      'dam_sire_dam_name',
        'dam_dam_id',           'dam_dam_name',
        'dam_dam_sire_id',      'dam_dam_sire_name',
        'dam_dam_dam_id',       'dam_dam_dam_name',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_lineages';

    // test
    private $unknown = "Unknown";

    /*
     * ASSOCIATING THE FAMILY CHARACTER MODELS
     */

    public function sire()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function sire_sire()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function sire_sire_sire()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function sire_sire_dam()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function sire_dam()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function sire_dam_sire()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function sire_dam_dam()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function dam()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function dam_sire()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function dam_sire_sire()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function dam_sire_dam()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function dam_dam()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function dam_dam_sire()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    public function dam_dam_dam()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    /**
     * Gets the display URL and/or name of an ancestor, or "Unknown" if there is none
     * @param   string  $ancestor
     * @return  string
     */
    public function getDisplayName($ancestor)
    {
        if(isset($this[$ancestor.'_id']) && $this[$ancestor])
            return $this[$ancestor]->getDisplayNameAttribute();

        if(isset($this[$ancestor.'_name']) && $this[$ancestor.'_name'])
            return $this[$ancestor.'_name'];

        return "Unknown";
    }

    /**
     * Gets characters with this character as their sire or dam
     *
     * @return array
     */
    public function getChildren($limit = false)
    {
        return CharacterLineage::getChildrenStatic($this->character_id, $limit);
    }

    /**
     * Gets characters with this character as their grand-sire or -dam
     *
     * @return array
     */
    public function getGrandchildren($limit = false)
    {
        return CharacterLineage::getGrandchildrenStatic($this->character_id, $limit);
    }

    /**
     * Gets characters with this character as their great-grand-sire or -dam
     *
     * @return array
     */
    public function getGreatGrandchildren($limit = false)
    {
        return CharacterLineage::getGreatGrandchildrenStatic($this->character_id, $limit);
    }

    /**
     * Gets characters with this character as their sire or dam
     *
     * @return array
     */
    public static function getChildrenStatic($id, $limit = false)
    {
        // Get the id numbers of the children.
        $ids = CharacterLineage::where('sire_id', $id)->orWhere('dam_id', $id)
                ->pluck('character_id')->toArray();
        return CharacterLineage::filterDescendants($ids, $limit);
    }

    /**
     * Gets characters with this character as their grand-sire or -dam
     *
     * @return array
     */
    public static function getGrandchildrenStatic($id, $limit = false)
    {
        // Get the id numbers of the children.
        $ids = CharacterLineage::where('sire_sire_id', $id)
                ->orWhere('sire_dam_id', $id)
                ->orWhere('dam_sire_id', $id)
                ->orWhere('dam_dam_id', $id)
                ->pluck('character_id')->toArray();
        return CharacterLineage::filterDescendants($ids, $limit);
    }

    /**
     * Gets characters with this character as their grand-sire or -dam
     *
     * @return array
     */
    public static function getGreatGrandchildrenStatic($id, $limit = false)
    {
        // Get the id numbers of the children.
        $ids = CharacterLineage::where('sire_sire_sire_id', $id)
                ->orWhere('sire_sire_dam_id', $id)
                ->orWhere('sire_dam_sire_id', $id)
                ->orWhere('sire_dam_dam_id', $id)
                ->orWhere('dam_sire_sire_id', $id)
                ->orWhere('dam_sire_dam_id', $id)
                ->orWhere('dam_dam_sire_id', $id)
                ->orWhere('dam_dam_dam_id', $id)
                ->pluck('character_id')->toArray();
        return CharacterLineage::filterDescendants($ids, $limit);
    }

    /**
     * Gets filtered descendents from id array
     *
     * @return array
     */
    public static function getFilteredDescendants($children_ids)
    {
        return Character::whereIn('characters.id', $children_ids)
            ->where('characters.is_visible', true)
            ->where(function($query) {
                $query->whereNull('character_category_id')
                      ->orWhereNotIn('character_category_id', CharacterLineageBlacklist::getBlacklistCategories(true));
            })
            ->whereNotIn('rarity_id', CharacterLineageBlacklist::getBlacklistRarities(true))
            ->join('character_images', 'characters.character_image_id', '=', 'character_images.id')
            ->where(function($query) {
                $query->whereNull('species_id')
                      ->orWhereNotIn('species_id', CharacterLineageBlacklist::getBlacklistSpecies(true));
            })
            ->where(function($query) {
                $query->whereNull('subtype_id')
                      ->orWhereNotIn('subtype_id', CharacterLineageBlacklist::getBlacklistSubtypes(true));
            })
            ->orderBy('is_myo_slot', 'asc');
    }

    /**
     * Gets filtered descendents from id array
     *
     * @return array
     */
    public static function filterDescendants($ids, $limit)
    {
        // If null or 0, return null.
        if ($ids == null || count($ids) < 1) return null;

        // Find characters matching those ids.
        $children = Character::whereIn('id', $ids);
        if(!Auth::check() || !(Auth::check() && Auth::user()->hasPower('manage_characters'))) $children->where('is_visible', true);

        // Sort, limit and return.
        $children->orderBy('is_myo_slot', 'asc')->orderBy('id', 'desc');
        if($limit) $children->limit(4);
        return $children->get();
    }
}
