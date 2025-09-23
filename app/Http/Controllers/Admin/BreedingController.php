<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Base\Base;
use App\Models\Feature\FeatureCategory;
use App\Models\Marking\Marking;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;

class BreedingController extends Controller {
    public function getBreedingSettings() {
        return view('admin.breedings._edit_settings', [
            'species'            => ['Select Species'] + Species::all()->pluck('name', 'id')->toArray(),
            'subtypes'           => ['Select Subtype'] + Subtype::all()->pluck('name', 'id')->toArray(),
            'featureCategories'  => FeatureCategory::all()->pluck('name', 'id')->toArray(),
            'markings'           => ['Select Marking'] + Marking::all()->pluck('name', 'id')->toArray(),
            'rarities'           => ['Select Rarity'] + Rarity::all()->pluck('name', 'id')->toArray(),
            'bases'              => ['Select Base'] + Base::all()->pluck('name', 'id')->toArray(),
        ]);
        //DB::table('site_settings')->where('key', $key)->update(['value' => $request->get('value')])
    }
}
