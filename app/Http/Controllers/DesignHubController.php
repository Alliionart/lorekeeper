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
        $markings = self::getDesignHubGenetics($request);
        $rarities = Rarity::whereIn('id', $markings->keys())->get()->keyBy('id');
        $pages = SitePage::whereIn('key', ['dh-start', 'dh-end'])->get()->keyBy('id');

        return view('designhub.designhub', [
            'dh_start'          => $pages->where('key', 'dh-start')->first(),
            'dh_end'            => $pages->where('key', 'dh-end')->first(),
            'specieses'         => Species::where('is_visible', 1)->orderBy('sort', 'DESC')->get(),
            'subtypes'          => Subtype::where('is_visible', 1)->orderBy('sort', 'DESC')->get(),
            'markings'          => $markings,
            'rarity_list'       => $rarities,
            'corrupt_mutations' => self::getDesignHubTraitByCategory($request, Settings::get('corrupt_mutation_id')),
            'magical_mutations' => self::getDesignHubTraitByCategory($request, Settings::get('magical_mutation_id')),
        ]);
    }

    public function getDesignHubGenetics(Request $request) {
        $query = Marking::query();
        $data = $request->only([
            'name',
            'variant',
        ]);

        return $query->where('is_visible', 1)->orderBy('name', 'ASC')->paginate(200)->appends($request->query());
    }

    public function getDesignHubTraitByCategory(Request $request, $category_id) {
        $query = Feature::visible(Auth::check() ? Auth::user() : null)->with('category')->with('rarity')->with('species');
        $data = $request->only(['rarity_id', 'feature_category_id', 'species_id', 'subtype_id', 'name', 'sort']);

        return $query->orderBy('id')->where('feature_category_id', $category_id)->where('is_visible', 1)->paginate(20)->appends($request->query());
    }
}
