<?php

namespace App\Http\Controllers;

use App\Facades\Settings;
use App\Models\Feature\Feature;
use App\Models\Marking\Marking;
use App\Models\Rarity;
use App\Models\SitePage;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use Auth;
use Illuminate\Http\Request;

class DesignHubController extends Controller {
    public function getDesignHubPageView() {
        return view('designhub.designhub');
    }

    public function getDesignHubPage(Request $request) {
        $markings = Marking::visible()->with('rarity')->orderBy('name', 'ASC')->get()->sortByDesc('rarity.sort')->groupBy('rarity.id');
        $rarities = Rarity::whereIn('id', $markings->keys())->get()->keyBy('id');
        $trait_categories = Settings::get('designhub_trait_categories');
        $pages = SitePage::whereIn('key', ['dh-start', 'dh-end'])->get();

        if (str_contains(',', $trait_categories)) {
            $trait_categories = explode(',', $trait_categories);
        } else {
            $trait_categories = [$trait_categories];
        }

        return view('designhub.designhub', [
            'dh_start'          => $pages->where('key', 'dh-start')->first(),
            'dh_end'            => $pages->where('key', 'dh-end')->first(),
            'specieses'         => Species::orderBy('sort', 'DESC')->get(),
            'subtypes'          => Subtype::orderBy('sort', 'DESC')->get(),
            'markings'          => $markings,
            'rarity_list'       => $rarities,
            'trait_lists'       => $this->paginateDesignHubTraitByCategory($request, $trait_categories),
        ]);
    }

    private function paginateDesignHubTraitByCategory(Request $request, $category_ids) {
        $query = Feature::visible(Auth::check() ? Auth::user() : null)->with('category')->with('rarity')->with('species');

        return $query->orderBy('id')->whereIn('feature_category_id', $category_ids)->paginate(20)->appends($request->query());
    }
}
