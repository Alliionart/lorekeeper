<?php

namespace App\Http\Controllers;

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
