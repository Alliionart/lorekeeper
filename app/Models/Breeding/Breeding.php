<?php

namespace App\Models\Breeding;

use App\Models\Base\Base;
use App\Models\Carrier\Carrier;
use App\Models\Character\BreedingPermission;
use App\Models\Character\Character;
use App\Models\Character\CharacterImage;
use App\Models\Feature\Feature;
use App\Models\Item\Item;
use App\Models\Marking\Marking;
use App\Models\Model;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\User\User;
use App\Models\User\UserItem;
use Settings;

class Breeding extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'staff_id', 'status', 'character_data', 'breeding_data', 'results', 'rerolls',
        'parent_1', 'parent_2', 'permission_1', 'permission_2',
        'type', 'split_user_id', 'split_status',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'breeding';

    /**
     * Item modifiers for checks.
     *
     * @var int
     */
    protected $item_mods;

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'user_id'             => 'required',
        'status'              => 'default:Pending',
        'parent_1'            => 'required',
        'parent_2'            => 'required',
        'character_data'      => 'nullable',
        'breeding_data'       => 'nullable',
        'type'                => 'required',
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

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->item_mods = json_decode(Settings::get('breeding_modifiers'));
    }

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the breeding.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the split user who owns the breeding.
     */
    public function split_user() {
        return $this->belongsTo(User::class, 'split_user_id');
    }

    /**
     * Get the admin who processed the breeding.
     */
    public function staff() {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get the dynamic parents.
     *
     * @param mixed $i
     */
    public function parent($i = 1) {
        return $this->belongsTo(Character::class, 'parent_'.$i);
    }

    public function parent1() {
        return $this->belongsTo(Character::class, 'parent_1');
    }

    public function parent2() {
        return $this->belongsTo(Character::class, 'parent_2');
    }

    /**
     * Get the user who owns the character.
     *
     * @param mixed $i
     */
    public function permission($i = 1) {
        return $this->belongsTo(BreedingPermission::class, 'permission_'.$i);
    }

    public function permission1() {
        return $this->belongsTo(BreedingPermission::class, 'permission_1');
    }

    public function permission2() {
        return $this->belongsTo(BreedingPermission::class, 'permission_2');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort breeding entries by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('updated_at', 'DESC');
    }

    /**
     * Scope a query to sort breeding entries oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('updated_at');
    }

    /**
     * Scope a query where only pending breedings are shown.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query) {
        return $query->where('status', 'Pending');
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

    /**
     * Gets the breeding page's URL.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('breeding/'.$this->id);
    }

    /**
     * Gets the breeding page's URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/breedings/edit/'.$this->id);
    }

    public function getBreedingDataAttribute() {
        $breedingData = $this->attributes['breeding_data'] ?? null;

        return $breedingData ? json_decode($breedingData) : null;
    }

    public function getCharacterDataAttribute() {
        $character_data = $this->attributes['character_data'] ?? null;

        return $character_data ? json_decode($character_data) : null;
    }

    public function getUserItemsAttribute() {
        $data = $this->getBreedingDataAttribute();
        $user_items = $data->user->user_items ?? null;
        $items = [];
        if ($user_items && count((array) $user_items)) {
            foreach ($user_items as $stack_id => $row) {
                $stack = UserItem::where('id', $stack_id)->where('user_id', $this->user_id)->first();
                if (isset($row->value) && $row->value) {
                    $row->value = $this->getItemModifierModelObj($stack, $row->value);
                }
                $items[$stack_id] = (object) [
                    'quantity'  => $row->quantity ?? 1,
                    'userItem'  => $stack,
                    ...(isset($row->value) && $row->value ? ['value' => $row->value] : []),
                ];
            }

            return $items;
        }

        return [];
    }

    public function getResultsAttribute() {
        $data = $this->attributes['results'] ?? null;

        return $data ? json_decode($data) : null;
    }

    public function getResultsCountAttribute() {
        return count($this->getResultsAttribute()) ?? 0;
    }

    public function getFormattedCreatedAtAttribute() {
        return $this->created_at->format('F Y');
    }

    public function getParentsXAttribute() {
        $parent1 = $this->parent1;
        $parent2 = $this->parent2;
        if (!$parent1 || !$parent2) {
            return null;
        }

        return $parent1->displayName.' x '.$parent2->displayName;
    }

    public function getLitterResults($id = null) {
        $litter = $this->getResultsAttribute();

        if (!$litter || !count((array) $litter)) {
            return [];
        }

        if ($id !== null && isset($litter[$id])) {
            return $litter[$id];
        }

        $renderedLitter = [];
        foreach ($litter as $pup) {
            $renderedLitter[] = $this->getPupData($pup);
        }

        return $renderedLitter;
    }

    public function getAvailablePupCount() {
        $litter = $this->getResultsAttribute();
        $statuses = [];
        foreach ($litter as $pup) {
            $statuses[] = $this->getPupData($pup)->status ?? null;
        }
        $countAvailablePups = count(array_filter($statuses, function ($status) {
            return $status === null;
        }));

        return $countAvailablePups;
    }

    public function getPupData($pup) {
        $is_chimera = str_contains($pup->base, '|');
        $renderedPup = [
            'is_chimera'    => $is_chimera,
            'species'       => Species::find($pup->species),
            'subtype'       => Subtype::find($pup->subtype),
            'traits'        => (object) [
                'ears'      => Feature::find($pup->traits->ears),
                'eyes'      => Feature::find($pup->traits->eyes),
                'tail'      => Feature::find($pup->traits->tail),
                'fur'       => Feature::find($pup->traits->fur),
            ],
            'status'        => $pup->status ?? null,
        ];

        if ($is_chimera) {
            $bases = explode('|', $pup['base']);
            $base = [
                0 => Base::find($bases[0]),
                1 => Base::find($bases[1]),
            ];

            $side_0 = $this->get_markings($pup->markings[0]);
            $side_1 = $this->get_markings($pup->markings[1]);

            $renderedPup['raw_markings'][0] = $side_0;
            $renderedPup['markings'][0] = $this->renderMarkingSide($side_0, $base[0]);
            $renderedPup['raw_markings'][1] = $side_1;
            $renderedPup['markings'][1] = $this->renderMarkingSide($side_1, $base[1]);
        } else {
            $base = Base::find($pup->base);

            $side = $this->get_markings($pup->markings);
            $renderedPup['raw_markings'] = $side;
            $renderedPup['markings'] = $this->renderMarkingSide($side, $base);
        }
        $renderedPup['base'] = $base;
        if (isset($pup->inbreeding) && $pup->inbreeding) {
            $inbred_trait = Feature::find($pup->inbreeding);
            $renderedPup['inbred'] = $inbred_trait;
        }
        if (isset($pup->mutations) && $pup->mutations) {
            $mutation = Feature::find($pup->mutations);
            $renderedPup['mutation'] = $mutation;
        }
        if (isset($pup->carriers) && isset($pup->carriers->active) && count((array) $pup->carriers->active)) {
            $renderedPup['carriers'] = Carrier::whereIn('id', (array) $pup->carriers->active)->get();
        }

        return (object) $renderedPup;
    }

    public function renderMarkingSide($markings, $base) {
        $genotype = $base->code.'+';
        $phenotype = '';

        $gMarkings = [];
        $pMarkings = [];

        if ($markings && count($markings)) {
            foreach ($markings as $g => $group) {
                foreach ($group as $m) {
                    $code = $m['code'];
                    $color = $m['marking']->rarity->color;
                    $name = '<a href="'.$m['marking']->url.'" style="text-decoration-color:#'.$color.'">'.$m['marking']->name.'</a>';

                    $gMarkings[] = $code;
                    $pMarkings[$g][] = $name;
                }
            }
            $genotype = $genotype.implode('/', $gMarkings);
            ksort($pMarkings);
            $phenotype = '';
            $after_base = isset($pMarkings[1]);
            $base_added = false;
            $base = $base->name ?? $base;
            foreach ($pMarkings as $i => $value) {
                if (is_array($value)) {
                    $value = implode(' ', $value);
                }
                if ($i === 1 && !$base_added) {
                    $value = ' '.$base.($after_base ? ' with ' : '').$value;
                    $base_added = true;
                }
                if ($i === 9) {
                    $value = 'and '.$value;
                }
                $phenotype .= ' '.$value;
            }
            if (!$base_added) {
                $phenotype .= ' '.$base;
            }
        } else {
            $phenotype = $base->name;
        }

        return [
            'g' => $genotype,
            'p' => $phenotype,
        ];
    }

    public function updatePup($i, $pup) {
        $litter = $this->getResultsAttribute();
        $litter[$i] = $pup;

        $this->update([
            'results' => json_encode($litter),
        ]);
    }

    public function getPupValue($pup, $exchange) {
        switch ($exchange) {
            case 'flat_krones':
                $amount = 1000;
                $currency_id = 1;
                break;
            case 'flat_trinkets':
                $amount = 1;
                $currency_id = 8;
                break;
            case 'full_krones':
                $amount = null;
                $currency_id = 1;
                break;
        }

        if ($amount > 0) {
            return (object) [
                'currency'  => $currency_id,
                'quantity'  => $amount,
            ];
        }

        // Start calculating amount here
        $totalcost = 0;
        $breakdown = [];

        $species = $pup->species;
        $subtype = $pup->subtype;

        if ($species && $subtype) {
            switch ($species->name) {
                case 'Vayron':
                    switch ($subtype->name) {
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
                    switch ($subtype->name) {
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

        $features = [
            0 => $pup->traits->ears,
            1 => $pup->traits->eyes,
            2 => $pup->traits->tail,
            3 => $pup->traits->fur,
        ];
        if (isset($pup->mutation)) {
            $features[] = $pup->mutation;
        }

        // Calculate worth based on feature rarities
        foreach ($features as $trait) {
            $rarity = $trait->rarity;
            $category = $trait->category;
            switch ($category->name) {
                case 'Eyes':
                case 'Ears':
                case 'Tail':
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

        //Flatten markings and format for valuation
        $flat_1 = $pup->is_chimera ? array_merge($pup->raw_markings[0], $pup->raw_markings[1]) : $pup->raw_markings;
        $cMarkings = array_merge(...$flat_1);

        if (count($cMarkings) < 4) {
            throw new \Exception('Pup cannot have less than 4 markings.');
        }
        if ($cMarkings) {
            foreach ($cMarkings as $m) {
                $marking = $m['marking'];
                $rarity = $marking->rarity;
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
                // Check if it exists, if not create it, if so then add count
                if (!array_key_exists('markings', $breakdown) || !array_key_exists($rarity->name, $breakdown['markings'])) {
                    $breakdown['markings'][$rarity->name] = [
                        'count' => 1,
                        'cost'  => $cost,
                    ];
                    $totalcost += $cost;
                } else {
                    $count = $breakdown['markings'][$rarity->name]['count'];
                    $oldCost = $breakdown['markings'][$rarity->name]['cost'];
                    $breakdown['markings'][$rarity->name]['count'] = $count + 1;
                    $breakdown['markings'][$rarity->name]['cost'] = $oldCost + $cost;
                    $totalcost += $cost;
                }
            }
        }

        return (object) [
            'currency'  => $currency_id,
            'quantity'  => $totalcost,
        ];
    }

    /**
     * @param mixed $id
     * @param mixed $i
     */
    public function get_parent($id, $i) {
        $character = Character::find($id);

        if (!$character) {
            return false;
        }

        $characterImage = CharacterImage::where('character_id', $character->id)->where('is_visible', 1)->first();
        $markings = $character->getMarkingFinalArray();
        $traits = $characterImage->features()->with('feature.category')->get();
        $trait_display = [];
        foreach ($traits as $feature) {
            $trait_display[$feature->feature->category->displayName] = $feature->feature->displayName;
        }

        $permission = $this->permission($i)->first();

        return (object) [
            'name'      => $character->fullName,
            'character' => $character,
            'id'        => $character->id,
            'species'   => $characterImage->species->displayName,
            'subtype'   => $characterImage->subtype->displayName ?? null,
            'image'     => $characterImage->getThumbnailUrlAttribute(),
            'phenotype' => strip_tags($character->getMarkingLinkedArray($markings)),
            'genotype'  => strip_tags($character->getMarkingLinkedArray($markings, 'genotype')),
            'traits'    => $trait_display,
            'lineage'   => $this->getViableLineageIds($character),
            'url'       => $character->getUrlAttribute(),
            'permission'=> $permission,
        ];
    }

    /**
     * @param mixed $character
     */
    public function getViableLineageIds($character) {
        if (!$character->lineage) {
            return [];
        }
        $lineage = [
            'sire_id'       => $character->lineage->sire_id,
            'sire_sire_id'  => $character->lineage->sire_sire_id,
            'sire_dam_id'   => $character->lineage->sire_dam_id,
            'dam_id'        => $character->lineage->dam_id,
            'dam_sire_id'   => $character->lineage->dam_sire_id,
            'dam_dam_id'    => $character->lineage->dam_dam_id,
        ];
        $final_ids = [];
        foreach ($lineage as $id) {
            if (!in_array($id, $final_ids) && $id) {
                $final_ids[$id] = Character::find($id)->displayName;
            }
        }

        return $final_ids;
    }

    public function checkInbreeding() {
        $lineage_1 = $this->getViableLineageIds($this->parent1);
        $lineage_2 = $this->getViableLineageIds($this->parent2);

        $common = array_intersect($lineage_1, $lineage_2);

        if (!empty($common)) {
            return $common;
        } else {
            return false;
        }
    }

    /**********************************************************************************************

        HELPER FUNCTIONS

    **********************************************************************************************/

    private function getItemModifierModelObj($stack, $value) {
        if (!$stack || !$value) {
            return false;
        }
        $item_id = $stack->item->id;
        $modifier = $this->item_mods->items->{$item_id};
        if ($modifier) {
            switch ($modifier->type) {
                case 'base_override':
                    return Base::find($value);
                    break;
                case 'subtype_override':
                    return Subtype::find($value);
                    break;
            }
        }

        return false;
    }

    private function get_markings(array $markings) {
        $rendered = [];
        if ($markings && count($markings)) {
            //Reorder first
            $ordered_markings = collect($markings)
                ->sortBy('name')
                ->groupBy('order')
                ->toArray();

            foreach ($ordered_markings as $order => $markings) {
                foreach ($markings as $marking) {
                    $m = Marking::find($marking->id);
                    $rendered[$order][] = [
                        'code'      => $marking->code,
                        'is_dom'    => $marking->code === $m->dominant ? true : false,
                        'marking'   => $m,
                        ...(isset($marking->base) && $marking->base ? ['base' => $base] : []),
                    ];
                }
            }

            return $rendered;
        }

        return [];
    }
}
