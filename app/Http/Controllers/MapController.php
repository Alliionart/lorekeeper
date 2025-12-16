<?php

namespace App\Http\Controllers;

use App\Models\SitePage;

class MapController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | World Map Controller
    |--------------------------------------------------------------------------
    |
    | Displays the world map and linked pages & content.
    |
    */

    /**
     * Shows the page with the given key.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getWorldMap() {
        // $page = SitePage::where('key', 'world-map')->where('is_visible', 1)->first();
        // if (!$page) {
        //     abort(404);
        // }

        return view('world.worldmap', [
            //'page' => $page
        ]);
    }
}
