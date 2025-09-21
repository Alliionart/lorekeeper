<?php

namespace App\Http\Controllers;

use App\Facades\Settings;
use App\Models\Feature\Feature;
use App\Models\Marking\Marking;
use App\Models\Rarity;
use App\Models\SitePage;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\Character\Character;
use App\Models\Character\CharacterImage;
use Auth;
use Illuminate\Http\Request;

class BreedingController extends Controller {
    public function getBreedingFormEntry() {
        return view('breeding.breeding_form');
    }

    // public function getBasePage(Request $request) {
    //     return view('designhub.basespage', [
    //         'bases'      => Base::where('is_visible', 1)->get(),
    //     ]);
    // }
}
