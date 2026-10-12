<?php

namespace App\Services;

use App\Models\Breeding\Breeding;
use App\Models\Feature\FeatureCategory;
use App\Models\Feature\Feature;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\Character\BreedingPermission;
use App\Models\Character\Character;
use App\Models\Character\CharacterImage;
use App\Models\Marking\Marking;
use App\Models\Carrier\Carrier;
use App\Models\Carrier\MarkingCarrier;
use App\Models\Item\Item;
use App\Models\Item\ItemTag;
use App\Models\User\UserItem;
use Illuminate\Support\Facades\DB;
use App\Models\Base\Base;
use Settings;
use Config;

class BreedingRollerService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Breeding Roller Service
    |--------------------------------------------------------------------------
    |
    | Handles the rolling and math of the rolling process.
    |
    */

    /**
     * ## INSTRUCTIONS ON HOW TO EDIT THIS FILE.
     * 
     * This entire file handles the logic for all breeding rolls and calculations.
     * Any changes to the breeding logic should be made here.
     * 
     * To start, you need to breakdown your own breeding process and understand exactly how you'd like to lay it out BEFORE modifying this file.
     * Keep in mind, when you add new parameters to a "pup" it will REQUIRE that you render those results in the Breeding model! This is always used to render.
     * 
     * This current file is an example of how I coded the World of Reos roller. You may use it as a reference for your own implementation.
     * 
     * How this setup works:
     * 1. From the BreedingService the rollBreeding with the breeding ALREADY attached in the construct. This is used to ensure all items and modifications are attached.
     * 2. The roll function is the entire breeding process and parent function. If you need to do anything BEFORE the actual rolling starts add it here. 
     *    You can add other parameters to the construct if you wish to "cache" it during the roll. For example, I cached the modifiers, and the parents' markings.
     * 3. 
     * 
     */

    /**
     * Set up all of the required variables & settings.
     */
    public function __construct($breeding) {
        $this->litter_conf          = json_decode(Settings::get('breeding_litter_config'));
        $this->inbreeding_rates     = json_decode(Settings::get('breeding_inbreeding_trait_rates'));
        $this->inbreeding_cat_id    = json_decode(Settings::get('breeding_inbreeding_trait_category_id'));
        $this->marking_rates        = json_decode(Settings::get('breeding_marking_rates'));
        $this->item_mods            = json_decode(Settings::get('breeding_modifiers'));
        $this->species_rates        = json_decode(Settings::get('breeding_species_rates'));
        $this->subtype_rates        = json_decode(Settings::get('breeding_subtype_rates'));
        $this->trait_rates          = json_decode(Settings::get('breeding_trait_rates'));
        $this->mutation_rates       = json_decode(Settings::get('breeding_mutation_rates'));
        $this->base_coats           = Base::where('is_visible', 1)->pluck('id')->toArray();

        $this->breeding         = $breeding ?? null;
        $this->items            = (array) $this->breeding->userItems ?? null;
        $this->p1               = (object) [];
        $this->p2               = (object) [];
        $this->markings_to_roll = null;
        $this->marking_objects  = [];
        $this->current_chimera  = false;
        $this->current_marks    = [];
        $this->current_species  = null;
        $this->inbreeding       = null;
        $this->carriers_to_check = null;
        $this->modifiers        = [];

        $this->debug = [];

    }

    /**
     * If an admin checks to remove items, this is run BEFORE the roll.
     */
    public function removeItems( $stacks ) {
        $stacks_ids = array_keys($stacks);
        $this->items = array_diff_key($this->items, array_flip($stacks_ids));
        
        return $this->items;
    }

    /**
     * The main entering function in order to roll the results between parents.
     */
    public function roll() {

        if ( ! $this->breeding ) {
            throw new \Exception('Cannot roll breeding: breeding record was not provided.');
        }

        $this->set_modifiers();
        $litter_size = $this->getLitterSize();
        
        // if ( !$this->checkGenders() ) {
        //     throw new \Exception('Characters of the same gender may not be bred without an Essence Vessel.');
        // } 
        
        if ( $litter_size < 1 ) {
            return [];
        }

        $results = [];
        for( $i = 0; $i < $litter_size; $i++ ) {
            $results[$i] = $this->rollPup();
        }

        return $results;
    }

    private function set_modifiers() {
        $new = [];
        if ( $this->items && count($this->items) && isset($this->item_mods->items) ) {
            $item_mods = (array) $this->item_mods->items;
            foreach($this->items as $stack_id => $row) {
                if ( !$row || !$row->userItem ) {
                    continue;
                }

                $item_id = $row->userItem->item->id;
                if (in_array($item_id, array_keys($item_mods))) {
                    $new[$item_mods[$item_id]->type] = [
                        'stack_id'  => $stack_id,
                        'value'     => $item_mods[$item_id]->rate ? (int) $item_mods[$item_id]->rate : ($row->value ?? null),
                    ];
                }
            }
            $this->modifiers = $new;
        }
    }

    /** 
     * Rolls the gender of a character.
     *
     * @return string 'male' or 'female'
     */
    private function rollGenders() {
        $is_male = $this->rollTF();
        return $is_male ? 'male' : 'female';

    }

    /**
     * Calculates the litter size determined by the parent species,
     * configured on the admin breeding settings.
     *
     * @return int The calculated litter size.
     */
    public function getLitterSize() {
        $parent_1 = Character::find( $this->breeding->parent_1 );
        $parent_2 = Character::find( $this->breeding->parent_2 );
        $empty_litter = false;
        $modifier_name = 'litter_size';
        $modifier = $this->modifiers[$modifier_name] ?? false;

        $this->set_parent(1, $parent_1);
        $this->set_parent(2, $parent_2);

        $parent_1_species = $parent_1->image->species;
        $parent_2_species = $parent_2->image->species;

        $parent_1_species_rates = $this->litter_conf->{$parent_1_species->id};
        $parent_2_species_rates = $this->litter_conf->{$parent_2_species->id};

        if ( $parent_1_species !== $parent_2_species ) {
            $mins = [
                $parent_1_species_rates->min ?? 1,
                $parent_2_species_rates->min ?? 3,
            ];
            $maxs = [
                $parent_1_species_rates->max ?? 1,
                $parent_2_species_rates->max ?? 3,
            ];

            $min = (int) min($mins);
            $max = (int) min($maxs);
        } else {
            $min = (int) $parent_1_species_rates->min;
            $max = (int) $parent_1_species_rates->max;
        }

        //Override if modifier present
        if ($modifier) {
            $min = $modifier['value'];
        }

        if ( $min === $max ) {
            return $min;
        }

        if ( $min < $max ) {
            return $this->rollRange($min, $max);
        }

        return 1;
    }

    private function rollPup() {
        $this->current_chimera = false;
        $this->current_marks = [];
        $species = $this->rollSpecies();
        if ( is_array($species) ) {
            $subtype = $species['subtype'];
            $species = $species['species'];
        }
        $this->current_species = $species;

        $pup = [
            'species'       => $species,
            'subtype'       => isset($subtype) ? $subtype : $this->rollSubtype($species),
            'traits'        => $this->rollTraits(),
            'inbreeding'    => $this->checkInbreeding(),
            'mutations'     => $this->checkMutations(),
            'markings'      => $this->handleMarkings(),
            'base'          => $this->rollBase(),
            'carriers'      => $this->checkCarriers(),
        ];

        return $pup;
    }

    /**
     * Rolls the species from parents.
     * 
     * @return int|array $id
     */
    private function rollSpecies() {
        $species_1 = $this->p1->character->image->species->id;
        $species_2 = $this->p2->character->image->species->id;

        if ( isset($this->modifiers['subtype_override']) ) {
            return [
                'species'   => $this->modifiers['subtype_override']['value']->species->id,
                'subtype'   => $this->modifiers['subtype_override']['value']->id,
            ];
        }

        if ( $species_1 === $species_2 ) return $species_1;

        $speciesIds = array_map('intval', [$species_1, $species_2]);
        sort($speciesIds);
        $rates = array_filter((array) $this->species_rates, function ($item) use ($speciesIds) {
            $keys = array_map('intval', array_keys((array) $item));
            sort($keys);

            return $keys == $speciesIds;
        });
        $rates = (array) array_first($rates);

        $rates = [
            0 => (object) [
                'rate'          => array_first($rates),
                'species_id'    => array_key_first($rates),
            ],
            1 => (object) [
                'rate'          => array_last($rates),
                'species_id'    => array_key_last($rates),
            ],
        ];

        return $rates[$this->rollWeightedList($rates)]->species_id;
    }

    /**
     * Rolls the subspecies from parents.
     * 
     * @return int $id
     */
    private function rollSubtype($species) {
        $species = (int) $species;
        $species_1 = (int) $this->p1->character->image->species->id;
        $species_2 = (int) $this->p2->character->image->species->id;
        $subtype_1 = $this->p1->character->image->subtype;
        $subtype_2 = $this->p2->character->image->subtype;
        $subtype_1_id = (int) $subtype_1->id;
        $subtype_2_id = (int) $subtype_2->id;

        if ($species_1 !== $species_2) {
            if ($species === $species_1) {
                $subtype = $subtype_1;
            } elseif ($species === $species_2) {
                $subtype = $subtype_2;
            } else {
                throw new \Exception('The rolled species does not match either parent species.');
            }

            if ((int) $subtype->species_id !== $species) {
                throw new \Exception('The parent subtype does not belong to the rolled species.');
            }

            return (int) $subtype->id;
        }

        if ($species !== $species_1) {
            throw new \Exception('The rolled species does not match the parent species.');
        }

        if ($subtype_1_id === $subtype_2_id) {
            return $subtype_1_id;
        }

        $parentIds = [$subtype_1_id, $subtype_2_id];
        sort($parentIds, SORT_NUMERIC);
        $index = null;
        foreach ((array) $this->subtype_rates as $rateIndex => $subtypeRate) {
            $configuredParentIds = array_map('intval', (array) ($subtypeRate->parent ?? []));
            sort($configuredParentIds, SORT_NUMERIC);

            if ($configuredParentIds === $parentIds) {
                $index = $rateIndex;
                break;
            }
        }
        if ($index === null) {
            throw new \Exception('No subtype rates are configured for the selected parent subtypes.');
        }

        $validSubtypeIds = Subtype::where('species_id', $species)->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();
        $results = array_values(array_filter($this->subtype_rates[$index]->results, function ($result) use ($validSubtypeIds) {
            return in_array((int) $result->subtype, $validSubtypeIds, true);
        }));
        if (!$results) {
            throw new \Exception('No configured subtype belongs to the rolled species.');
        }

        $rates = [];
        foreach ( $results as $result ) {
            $rates[] = (object) [
                'rate'          => (int) $result->rate,
                'subtype_id'    => (int) $result->subtype,
            ];
        }

        return $rates[$this->rollWeightedList($rates)]->subtype_id;
    }

    /**
     * Rolls the traits from parents.
     * 
     * @return int $id
     */
    private function rollTraits() {
        $p1 = $this->p1->character->image->features;
        $p2 = $this->p2->character->image->features;
        $traits = [
            'ears'  => null,
            'eyes'  => null,
            'tail'  => null,
            'fur'   => null,
        ];

        // Ears - Ensures that the rolled ears are compatible with the current species.
        $ear_category_id = config('lorekeeper.breeding.ear_category_id');
        $ear_rarities = [
            $p1->where('feature_category_id', $ear_category_id)->first()->rarity_id,
            $p2->where('feature_category_id', $ear_category_id)->first()->rarity_id
        ];
        $ear_rates = (array) $this->getTraitRates( $ear_category_id, $ear_rarities);
        $ear_rarity = data_get($ear_rates[$this->rollWeightedList($ear_rates)], 'rarity');
        $ears = Feature::where('rarity_id', $ear_rarity)->where('feature_category_id', $ear_category_id)->where('is_visible', 1)->where(function ($query) {
            $query->whereNull('species_id')
            ->orWhere('species_id', $this->current_species);
        })->pluck('id')->toArray();
        $traits['ears'] = $this->rand_list($ears);

        // Eyes - Has NO species checks
        $eye_category_id = config('lorekeeper.breeding.eye_category_id');
        $eye_rarities = [
            $p1->where('feature_category_id', $eye_category_id)->first()->rarity_id,
            $p2->where('feature_category_id', $eye_category_id)->first()->rarity_id
        ];
        $eye_rates = (array) $this->getTraitRates( $eye_category_id, $eye_rarities);
        $eye_rarity = data_get($eye_rates[$this->rollWeightedList($eye_rates)], 'rarity');
        $eyes = Feature::where('rarity_id', $eye_rarity)->where('feature_category_id', $eye_category_id)->where('is_visible', 1)->pluck('id')->toArray();
        $traits['eyes'] = $this->rand_list($eyes);

        // Tail - Ensures that the rolled tail is compatible with the current species.
        $tail_category_id = config('lorekeeper.breeding.tail_category_id');
        $tail_rarities = [
            $p1->where('feature_category_id', $tail_category_id)->first()->rarity_id,
            $p2->where('feature_category_id', $tail_category_id)->first()->rarity_id
        ];
        $tail_rates = (array) $this->getTraitRates( $tail_category_id, $tail_rarities);
        $tail_rarity = data_get($tail_rates[$this->rollWeightedList($tail_rates)], 'rarity');
        $tails = Feature::where('rarity_id', $tail_rarity)->where('feature_category_id', $tail_category_id)->where('is_visible', 1)->where(function ($query) {
            $query->whereNull('species_id')
            ->orWhere('species_id', $this->current_species);
        })->pluck('id')->toArray();
        $traits['tail'] = $this->rand_list($tails);

        // Fur - Has NO species checks
        $fur_category_id = config('lorekeeper.breeding.fur_category_id');
        $fur_rarities = [
            $p1->where('feature_category_id', $fur_category_id)->first()->rarity_id,
            $p2->where('feature_category_id', $fur_category_id)->first()->rarity_id
        ];
        $fur_rates = (array) $this->getTraitRates( $fur_category_id, $fur_rarities);
        $fur_rarity = data_get($fur_rates[$this->rollWeightedList($fur_rates)], 'rarity');
        $furs = Feature::where('rarity_id', $fur_rarity)->where('feature_category_id', $fur_category_id)->where('is_visible', 1)->pluck('id')->toArray();
        $traits['fur'] = $this->rand_list($furs);
        
        return $traits;
    }

    /**
     * Checks to see if a mutation has passed.
     * 
     * @return int|bool $id
     */
    private function checkMutations() {
        $category = null;
        $rarity = null;
        $aspectCategoryId = config('lorekeeper.breeding.aspect_category_id');
        $aspectRarityId = config('lorekeeper.breeding.aspect_rarity_id');
        if ( $this->mutation_rates && count(get_object_vars($this->mutation_rates)) ) {
            foreach( $this->mutation_rates as $type ) {
                if ($type->category == $aspectCategoryId && $type->rarity == $aspectRarityId) {
                    continue;
                }

                $passed = $this->rollPercent($type->rate);
                if ( $passed ) {
                    $category = $type->category;
                    $rarity = $type->rarity;
                    break;
                }
            }
        }
        if ( $category && $rarity ) {
            $mutations = Feature::where('is_visible', 1)->where('feature_category_id', $category)->where('rarity_id', $rarity)->pluck('id')->toArray();
            if ( $mutations && count($mutations) ) {
                return $this->rand_list($mutations);
            }
        }

        return false;
    }

    /**
     * Check's the parent's 2 recent lines of lineage for inbreeding.
     * 
     * @return int|bool
     */
    private function checkInbreeding() {
        if ( $this->inbreeding === null ) {
            $p1_lin = $this->p1->character->lineage()->first([
                'sire_id',
                'sire_sire_id',
                'sire_dam_id',
                'dam_id',
                'dam_sire_id',
                'dam_dam_id'
            ])?->toArray() ?? [];
            $p2_lin = $this->p2->character->lineage()->first([
                'sire_id',
                'sire_sire_id',
                'sire_dam_id',
                'dam_id',
                'dam_sire_id',
                'dam_dam_id'
            ])?->toArray() ?? [];

            $p1_lin = array_filter($p1_lin, fn($value) => !is_null($value));
            $p2_lin = array_filter($p2_lin, fn($value) => !is_null($value));

            $this->p1->lineage = Character::findMany($p1_lin);
            $this->p2->lineage = Character::findMany($p2_lin);

            $common = array_intersect($p1_lin, $p2_lin);
            
            if ( $common && count($common) ) {
                $this->inbreeding = true;
                throw new \Exception('Common ancestors found in the last two generations, unable to roll this breeding as it would result in inbreeding.');
            }
        }

        if ( $this->inbreeding ) {
            $rates = [];
            foreach ( (array) $this->inbreeding_rates as $feature_id => $rate ) {
                if ( $feature_id !== '0' || $feature_id !== 0 ) {
                    $rates[] = (object) [
                        'feature_id'    => $feature_id,
                        'rates'         => $rate,
                    ];
                }
            }
            return $rates[$this->rollWeightedList($rates)]->feature_id;
        }

        return false;
    }

    /**
     * Intercepts marking rolls to check for Chimera first.
     */
    private function handleMarkings() {
        if ( $this->current_chimera ) {
            return [
                0 => $this->rollMarkings(),
                1 => $this->rollMarkings(),
            ];
        }

        return $this->rollMarkings();
    }

    /**
     * Rolls markings.
     * 
     * @return int|bool $id
     */
    private function rollMarkings() {
        if ( !isset($this->p1->markings) ) {
            $this->set_parent_markings();
        }
        if ( !isset($this->p2->markings) ) {
            $this->set_parent_markings(2);
        }

        $p1 = $this->checkChimeras($this->p1, 1);
        $p2 = $this->checkChimeras($this->p2, 2);

        $markings = $this->set_markings_to_roll();
        
        // If either parent is chimera then forcibly select one side and save it back into the current marking context
        if ( $this->p1->is_chimera ) {
            $side = $this->rollRange(0,1);
            $markings[1] = $this->rand_list($markings[1][$side]);
            $this->p1->chimera_side_id = $side;
        }
        if ( $this->p2->is_chimera ) {
            $side = $this->rollRange(0,1);
            $markings[2] = $this->rand_list($markings[2][$side]);
            $this->p2->chimera_side_id = $side;
        }

        $unique_markings = $markings[1] + $markings[2];
        $marking_result = [];

        foreach( $unique_markings as $marking_id => $row ) {
            $rates = $this->getMarkingRates($row);
            $rolled = false;
            $dominant = false;
            $base = null;
            
            $this->debug['------ Starting to roll marking #'.$marking_id] = $rates['rate'].'% rolled '.$rates['quantity'].' times';

            // Roll to see if it passed
            for ( $i = 0; $i < $rates['quantity']; $i++ ) {
                $percent = $this->current_chimera ? $rates['rate'] / 2 : $rates['rate'];
                $rolled = $this->rollPercent($percent);
                if ( $rolled ) {
                    break 1;
                }
            }

            // Roll to see if dominant passes and if allowed
            if ( $rolled && $row['roll_dom'] ) {
                $rates = $this->getMarkingRates($row, true);

                $this->debug['TRY FOR DOM'] = $rates['rate'].'% rolled for dom '.$rates['quantity'].' times';

                for ( $i = 0; $i < $rates['quantity']; $i++ ) {
                    $dominant = $this->rollPercent($rates['rate']);
                    if ( $dominant ) {
                        break 1;
                    }
                }
            }

            if ( $rolled ) {
                $marking_result[$marking_id] = [
                    'dominant'  => $dominant,
                    'base'      => $base,
                ];
            }
        }

        $final_markings = [];

        if ( $marking_result && count($marking_result) ) {
            foreach( $marking_result as $id => $row ) {
                $marking = $this->marking_objects[$id];
                $nRow = [
                    'code'      => ( $row['dominant'] ? $marking->dominant : $marking->recessive ),
                    'is_dom'    => $row['dominant'],
                    'name'      => $marking->name,
                    'order'     => $marking->order_in_genome,
                    'id'        => $id,
                ];

                if ( ! in_array($id, $this->current_marks) ) {
                    $this->current_marks[] = $id;
                }
                
                if ( isset($row['base']) && $row['base'] ) {
                    $nRow['base'] = $row['base'];
                }
                $final_markings[] = $nRow;
            }
        }

        return $final_markings;
    }

    /**
     * Rolls the base with the shortest "path" between the parent coats.
     * Modify if you roll your coats differently!
     *
     * @return int|array The rolled base ID, or an array of base IDs if the current chimera is active.
     */
    private function rollBase() {
        $p1_base = (int) $this->p1->character->base;
        $p2_base = (int) $this->p2->character->base;

        $max = count($this->base_coats);

        // Assign the correct chimera sides of they exist
        if ( str_contains($p1_base, '|') ) {
            $p1_base = (int) explode('|', $p1_base)[$this->p1->chimera_side_id];
        }
        if ( str_contains($p2_base, '|') ) {
            $p2_base = (int) explode('|', $p2_base)[$this->p2->chimera_side_id];
        }

        $start = min($p1_base, $p2_base);
        $end = max($p1_base, $p2_base);
        $length = $end - $start + 1;
        $wrapLength = ($max - $end) + $start + 2;

        if ( $length <= $wrapLength ) {
            $range = range($start, $end);
        } else {
            $range = array_merge(range($end, $max), range(1, $start));
        }

        // Only roll IDs for visible base coats; zero is not a valid base ID.
        $range = array_values(array_intersect($range, $this->base_coats));
        if ( !count($range) ) {
            $range = $this->base_coats;
        }

        if ( isset($this->modifiers['base_override']) ) {
            return $this->current_chimera ? [
                1 => $this->modifiers['base_override']['value']->id,
                2 => $this->rand_list($range),
            ] : $this->modifiers['base_override']['value']->id;
        } 

        return $this->current_chimera ? [
            1   => $this->rand_list($range),
            2   => $this->rand_list($range),
        ] : $this->rand_list( $range );
    }

    private function checkCarriers() {
        if ( $this->carriers_to_check === null ) {
            $carrier_ids = array_merge(
                $this->p1->character->carriers->pluck('carrier_id')->toArray(),
                $this->p2->character->carriers->pluck('carrier_id')->toArray(),
            );

            foreach( $this->p1->lineage as $character ) {
                $carrier_ids = $carrier_ids + $character->carriers->pluck('carrier_id')->toArray();
            }
            foreach( $this->p2->lineage as $character ) {
                $carrier_ids = $carrier_ids + $character->carriers->pluck('carrier_id')->toArray();
            }

            if ( $carrier_ids && count($carrier_ids) ) {
                $carriers = Carrier::find($carrier_ids);
                $this->carriers_to_check = $carriers;
            } else {
                $this->carriers_to_check = false;
            }
        }

        if ($this->carriers_to_check && count($this->carriers_to_check)) {
            $carriers = [
                'active'    => [],
                'inactive'  => [],
            ];
            foreach( $this->carriers_to_check as $carrier ) { 
                $applicable_markings = MarkingCarrier::where('carrier_id', $carrier->id)
                    ->pluck('marking_id')
                    ->toArray();

                $matched = array_values(array_intersect($applicable_markings, $this->current_marks));
                $not_matched = array_values(array_diff($applicable_markings, $this->current_marks));

                if (!empty($matched)) { 
                    $carriers['active'] = array_merge($carriers['active'] ?? [], [$carrier->id]); 
                } else { 
                    $carriers['inactive'] = array_merge($carriers['inactive'] ?? [], [$carrier->id]); 
                }
            }
            return $carriers;
        }

        return false;
    }

    /** -------------------------------
     *      SETTERS
     *      Set up saved variables such as parent's in case it saves a query.
     * -------------------------------
     */

    /**
     * Set the parent character.
     *
     * @param int $i The parent number (1 or 2).
     * @param Character $character The character to set as the parent.
     */
    private function set_parent($i, $character) {
        $p = 'p'.$i;
        $this->{$p}->character = $character;
    }

    /**
     * Caches the parent markings for the entire roll.
     */
    private function set_parent_markings($p = 1, $markings = null) {
        if ( $p == 1 ) {
            if ( $markings ) {
                $cm_1 = $markings;
            } else {
                $cm_1 = $this->p1->character->markings()->get()->map->only(['marking_id', 'code', 'is_dominant', 'data'])->toArray();
            }
            
            $this->p1->markings = $cm_1;
            $this->p1->is_chimera = false;
        }
        if ( $p == 2 ) {
            if ( $markings ) {
                $cm_2 = $markings;
            } else {
                $cm_2 = $this->p2->character->markings()->get()->map->only(['marking_id', 'code', 'is_dominant', 'data'])->toArray();
            }
            $this->p2->markings = $cm_2;
            $this->p2->is_chimera = false;
        }
    }

    /**
     * Sets up the markings to roll, AKA if rolling recessive, dominant, etc.
     *
     * @return array The markings to roll.
     */
    private function set_markings_to_roll() {
        if ( $this->markings_to_roll ) {
            return $this->markings_to_roll;
        }

        $m1 = $this->p1->markings;
        $m2 = $this->p2->markings;
        $markings_to_roll = [];

        // Flatter the markings and get their ids to compare
        $flattenMarkings = function ($markings) {
            if ( !is_array($markings) ) {
                return [];
            }
            $flat = [];

            foreach ( $markings as $group ) {
                if ( !is_array($group) ) continue;
                if ( array_key_exists('marking_id', $group) ) {
                    $flat[] = $group;
                    continue;
                }
                $flat = array_merge($flat, $group);
            }
            return $flat;
        };

        $m1_ids = array_unique(array_column($flattenMarkings($m1), 'marking_id'));
        $m2_ids = array_unique(array_column($flattenMarkings($m2), 'marking_id'));

        // Markings both parents have
        $double_marks = array_values(array_intersect($m1_ids, $m2_ids));
        if ( $double_marks && count($double_marks) ) {
            foreach($double_marks as $dm_id) {
                //Fetch both parent's versions of the marking
                $dm_1 = array_first($this->findMarkingById($m1, $dm_id));
                $dm_2 = array_first($this->findMarkingById($m2, $dm_id));
                $marking = Marking::find($dm_id);

                //Cache the marking if unique
                if ( ! in_array($dm_id, array_keys($this->marking_objects)) ) {
                    $this->marking_objects[$dm_id] = $marking;
                }

                $marking_codes = $marking->only(['recessive', 'dominant']);
                $rarity = $marking->rarity->name;

                // Create the key
                $parentCodes = [
                    $dm_1['is_dominant'] ? 'dom' : 'rec',
                    $dm_2['is_dominant'] ? 'dom' : 'rec',
                ];
                sort($parentCodes);
                $key = implode('X', $parentCodes);

                for ($i = 1; $i <= 2; $i++) {
                    $p = 'p' . $i;
                    $ddm = 'dm_' . $i;
                    
                    if ($this->{$p}->is_chimera) {
                        $markings_to_roll[$i][$$ddm['data']][$dm_id] = [
                            'roll_dom' => true,
                            'rates'    => $this->marking_rates->{$rarity}->{$key},
                        ];
                    } else {
                        $markings_to_roll[$i][$dm_id] = [
                            'roll_dom'      => true,
                            'rates'         => $this->marking_rates->{$rarity}->{$key},
                        ];
                    }
                }
            }
        }

        //Markings unique to one parent
        $m1_only = array_values(array_filter(
            $flattenMarkings($m1),
            fn ($marking) => !in_array($marking['marking_id'], $m2_ids, true)
        ));
        $m2_only = array_values(array_filter(
            $flattenMarkings($m2),
            fn ($marking) => !in_array($marking['marking_id'], $m1_ids, true)
        ));
        $single_marks = array_merge($m1_only, $m2_only);
        if ( $single_marks && count($single_marks) ) {
            foreach($single_marks as $sm) {
                $marking = Marking::find($sm['marking_id']);

                //Cache the marking if unique
                if ( ! in_array($sm['marking_id'], array_keys($this->marking_objects)) ) {
                    $this->marking_objects[ $sm['marking_id'] ] = $marking;
                }

                $marking_codes = $marking->only(['recessive', 'dominant']);
                $rarity = $marking->rarity->name;
                $key = ($sm['is_dominant'] ? 'dom' : 'rec') . 'Xnon';

                for ($i = 1; $i <= 2; $i++) {
                    $p = 'p' . $i;
                    $ddm = 'dm_' . $i;
                    
                    if ($this->{$p}->is_chimera) {
                        $markings_to_roll[$i][$sm['marking_id']] = [
                            'roll_dom'      => $sm['is_dominant'] ? true : false,
                            'rates'         => $this->marking_rates->{$rarity}->{$key},
                        ];
                    } else {
                        $markings_to_roll[$i][$sm['marking_id']] = [
                            'roll_dom'      => $sm['is_dominant'] ? true : false,
                            'rates'         => $this->marking_rates->{$rarity}->{$key},
                        ];
                    }
                }
            }
        }

        $this->markings_to_roll = $markings_to_roll;

        return $markings_to_roll;
    }

    /**
     * Check the genders of the parent characters to ensure compatibility.
     * This function is disabled as there is no need to enforce a core table. Migrate and create your own to start!
     *
     * @return bool True if the genders are compatible, false otherwise.
     */
    // private function checkGenders() {
    //     $p1_sex = $this->p1->character->sex;
    //     $p2_sex = $this->p2->character->sex;

    //     if ( $p1_sex === $p2_sex ) {
    //         if ( isset($this->modifiers['sex_override']) ) {
    //             return true;
    //         }
    //         return false;
    //     }

    //     return true;
    // }
    
    /** -------------------------------
     *      HELPER FUNCTIONS
     *      Functions to purely help with the math and randomization.
     * -------------------------------
     */

    /**
     * Finds the rates for A x B from the admin breeding settings.
     */
    private function getTraitRates( int|string $category_id, array $rarities ) {
        $filtered = array_filter($this->trait_rates, function($item) use ($category_id, $rarities) {
            $category_match = in_array($category_id, $item->feature_categories);
            $rarity_match = empty(array_diff($rarities, $item->rarity));
            return $category_match && $rarity_match;
        });

        return array_first($filtered)->rarity_results;
    }

    /**
     * Gets the marking rates for a given row, optionally for dominant markings.
     */
    private function getMarkingRates( $row, $get_dom = false ) {
        $quantity = 1;

        if ( ! $get_dom ) {
            $roll_rate = $row['rates']->rate;

            if ( str_contains($roll_rate, 'x') ) {
                $roll_rate = explode('x', $roll_rate);
                $quantity = (int) $roll_rate[1];
                $roll_rate = (int) $roll_rate[0];
            } else {
                $roll_rate = (int) $roll_rate;
            }

            return [
                'rate'      => $roll_rate,
                'quantity'  => $quantity,
            ];
        } else {
            $dom_rate = (int) $row['rates']->roll_dom;

            if ( str_contains($dom_rate, 'x') ) {
                $dom_rate = explode('x', $dom_rate);
                $quantity = (int) $dom_rate[1];
                $dom_rate = (int) $dom_rate[0];
            } else {
                $dom_rate = (int) $dom_rate;
            }

            return [
                'rate'      => $dom_rate,
                'quantity'  => $quantity,
            ];
        }
    }

    /**
     * Find a marking by the ID from the cached array.
     */
    private function findMarkingById( $markings_array, $id ) {
        return array_filter($markings_array, function($marking) use ($id) {
            return $marking['marking_id'] === $id;
        }); 
    }

    /**
     * If one parent is chimera, pick 1 side to roll. This is Reos-specific logic for handling chimera markings.
     */
    private function checkChimeras( $p, $id ) {
        $is_chimera = str_contains($p->character->base, '|');

        if ( $is_chimera ) {
            $column = 'data';
            $markings = $p->markings;
            $split_markings = [];
            for ( $i = 0; $i <= 1; $i++ ) {
                $split_markings[$i] = array_filter($markings, function($subA) use ($column, $i) {
                    return isset($subA[$column]) && $subA[$column] === (string) $i;
                });
            }
            $markings = $split_markings;
            $pval = 'p'.$id;
            $this->{$pval}->is_chimera = true;
            $this->set_parent_markings($id, $markings);
            return $markings;
        }

        return $p->markings;
    }

    /**
     * Roll a random base from the available base coats.
     */
    private function getRandomBase() {
        return $this->rand_list($this->base_coats);
    }

    /** ----------------- MATH --------------- */

    /**
     * Roll a random integer within the specified range, inclusive.
     */
    private function rollRange( int $min, int $max ) {
        return mt_rand( $min, $max );
    }

    /**
     * Roll a true/false value with a 50/50 chance.
     */
    private function rollTF() {
        return (bool) mt_rand( 0, 1 );
    }

    /**
     * Cherry-pick a random element from the array.
     */
    private function rand_list($array) {
        $array = (array) $array;
        $rIndex = mt_rand(0, count($array) - 1);
        return $array[$rIndex];
    }

    /**
     * Roll a percentage (100%)
     */
    private function rollPercent($percentage) {
        $percentage = (float) $percentage;

        if ($percentage <= 0) return false;
        if ($percentage >= 100) return true;

        $target = (int) ($percentage * 1000);
        $roll = random_int(1, 100000);

        $this->debug[] = 'Number rolled: ' . ($roll / 1000);

        return $roll <= $target;
    }

    /**
     * Uses the array to weigh the rates in a group and returns the rolled group.
     * 
     * @param array|object $items      Must be an array of objects with "rate" parameter.
     * @param string $column    Name of the column to look in
     */
    private function rollWeightedList(array|object $items) {
        $items = (array) $items;
        $roll = mt_rand(1, 100);

        foreach ( $items as $key => $item ) {
            $roll -= (int) data_get($item, 'rate');
            if ( $roll <= 0 ) {
                return $key;
            }
        }

        return array_key_last($items);
    }
    
}
