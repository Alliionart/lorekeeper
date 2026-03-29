<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Base\Base;
use App\Models\Feature\Feature;
use App\Models\Feature\FeatureCategory;
use App\Models\Item\Item;
use App\Models\Marking\Marking;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Services\BreedingService;
use DB;
use Illuminate\Http\Request;

class BreedingController extends Controller {
    public function getBreedingSettings() {
        return view('admin.breedings._edit_settings', [
            'species'            => ['Select Species'] + Species::all()->pluck('name', 'id')->toArray(),
            'subtypes'           => ['Select Subtype'] + Subtype::all()->pluck('name', 'id')->toArray(),
            'featureCategories'  => FeatureCategory::all()->pluck('name', 'id')->toArray(),
            'markings'           => ['Select Marking'] + Marking::all()->pluck('name', 'id')->toArray(),
            'markingRarities'    => Rarity::wherein('id', Marking::distinct()->pluck('rarity_id')->toArray())->pluck('name', 'id')->toArray(),
            'rarities'           => ['Select Rarity'] + Rarity::all()->pluck('name', 'id')->toArray(),
            'bases'              => ['Select Base'] + Base::all()->pluck('name', 'id')->toArray(),
            'items'              => ['Select Item'] + Item::where('item_category_id', DB::table('site_settings')->where('key', 'breeding_item_category_id')->value('value'))->pluck('name', 'id')->toArray(),
            'skillRarities'      => Rarity::wherein('id', DB::table('skills')->distinct()->pluck('rarity_id')->toArray())->pluck('name', 'id')->toArray(),
            'inbreeding_traits'  => [0 => 'Stillborn'] + Feature::where('feature_category_id', DB::table('site_settings')->where('key', 'breeding_inbreeding_trait_category_id')->value('value'))->pluck('name', 'id')->toArray(),
            'markingConfig'      => [
                ['recessive' => null],
                ['recessive' => 'recessive'],
                ['dominant'  => null],
                ['dominant'  => 'recessive'],
                ['dominant'  => 'dominant'],
            ],
            'modifier_types'    => [
                ''                 => 'Select modifier...',
                'litter_size'      => 'Min Litter Size (Number or Range(1-5))',         //Changes the number of offspring produced, can be a flat number for minimum or a range, e.g. 3-5
                'sex_ratio'        => 'Sex Ratio (F%|M%)',                              //Changes the ratio of female to male offspring, format should be female:male, e.g. 50:50
                'subtype_override' => 'Override Subtype (Species ID)',                  //Overrides the subtype of the offspring to a specific subtype
                'sex_override'     => 'Same Gender may Mate (Male|Female)',             //Allows parents to be same gender
                'base_override'    => 'Guarantee Base (Base ID)',                       //Overrides the base of the offspring to a specific base
                'convert_split'    => 'Convert Split Slot',                             //Overrides the base of the offspring to a specific base
            ],
            'currentSettings'   => [
                'species_rates'     => $this->getBreedingSetting('breeding_species_rates'),
                'marking_rates'     => $this->getBreedingSetting('breeding_marking_rates'),
                'litter_config'     => $this->getBreedingSetting('breeding_litter_config'),
                'mutation_rates'    => $this->getBreedingSetting('breeding_mutation_rates'),
                'subtype_rates'     => $this->getBreedingSetting('breeding_subtype_rates'),
                'trait_rates'       => $this->getBreedingSetting('breeding_trait_rates'),
            ],
        ]);
    }

    public function getBreedingSetting($key) {
        $exists = DB::table('site_settings')->where('key', $key)->pluck('value');
        if (count($exists) > 0) {
            return json_decode($exists[0]);
        }

        return null;
    }

    public function postBreedingSettings(Request $request, BreedingService $service) {
        $data = $request->all();

        if ($service->updateBreedingSettings($data, auth()->user())) {
            flash('Breeding settings updated successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
