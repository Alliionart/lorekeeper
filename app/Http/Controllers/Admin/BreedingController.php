<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Base\Base;
use App\Models\Breeding\Breeding;
use App\Models\Feature\Feature;
use App\Models\Feature\FeatureCategory;
use App\Models\Item\Item;
use App\Models\Marking\Marking;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\User\User;
use App\Services\BreedingService;
use DB;
use Illuminate\Http\Request;

class BreedingController extends Controller {
    /**
     * Shows the breeding index page.
     *
     * @param string $status
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBreedingIndex(Request $request, $status = 'Pending') {
        $breedings = Breeding::where('status', $status ? ucfirst($status) : 'Pending')
            ->whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotIn('split_status', ['Pending', 'Rejected'])
                    ->orWhereNull('split_status');
            });
        $data = $request->only(['user_ids', 'month', 'sort']);
        $breedsByMonth = $breedings->get()->sortBy('created_at')->mapWithKeys(function ($breeding) {
            return [$breeding->created_at->format('Y-m') => $breeding->created_at->format('M Y')];
        })->toArray();

        if (isset($data['user_ids']) && $data['user_ids'] != 'none') {
            $breedings->whereHas('user', function ($query) use ($data) {
                $query->whereIn('id', explode(',', $data['user_ids']));
            });
        }

        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'newest':
                    $breedings->sortNewest();
                    break;
                case 'oldest':
                    $breedings->sortOldest();
                    break;
            }
        } else {
            $breedings->sortOldest();
        }
        if (isset($data['month']) && $data['month'] !== '') {
            $breedings = $breedings->get()->filter(function ($breeding) use ($data) {
                return $breeding->created_at->format('Y-m') === $data['month'];
            });
        }

        $submission_users = $breedings->pluck('user_id')->unique()->values()->toArray();

        return view('admin.breedings.index', [
            'breedings'     => $breedings->paginate(30)->appends($request->query()),
            'users'         => ['none' => 'Any User'] + User::whereIn('id', $submission_users)->pluck('name', 'id')->toArray(),
            'months'        => ['' => 'Any Month'] + $breedsByMonth,
        ]);
    }

    /**
     * Shows the breeding detail page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBreeding($id) {
        $breeding = Breeding::find($id);
        if (!$breeding) {
            abort(404);
        }

        return view('admin.breedings.edit_breeding', [
            'breeding'          => $breeding,
        ]);
    }

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
                'litter_size'      => 'Min Litter Size (Number or Range(1-5))',         // Changes the number of offspring produced, can be a flat number for minimum or a range, e.g. 3-5
                'sex_ratio'        => 'Sex Ratio (F%|M%)',                              // Changes the ratio of female to male offspring, format should be female:male, e.g. 50:50
                'subtype_override' => 'Override Subtype (Species ID)',                  // Overrides the subtype of the offspring to a specific subtype
                'sex_override'     => 'Same Gender may Mate (Male|Female)',             // Allows parents to be same gender
                'base_override'    => 'Guarantee Base (Base ID)',                       // Overrides the base of the offspring to a specific base
                'convert_split'    => 'Convert Split Slot',                             // Overrides the base of the offspring to a specific base
                'override_glint'   => 'Override Glint',                                 // Overrides the glint of the offspring to a chosen color if passed
            ],
            'currentSettings'   => [
                'species_rates'     => $this->getBreedingSetting('breeding_species_rates'),
                'marking_rates'     => $this->getBreedingSetting('breeding_marking_rates'),
                'litter_config'     => $this->getBreedingSetting('breeding_litter_config'),
                'mutation_rates'    => $this->getBreedingSetting('breeding_mutation_rates'),
                'subtype_rates'     => $this->getBreedingSetting('breeding_subtype_rates'),
                'trait_rates'       => $this->getBreedingSetting('breeding_trait_rates'),
                'skill_rates'       => $this->getBreedingSetting('breeding_skills_rates'),
                'modifiers'         => $this->getBreedingSetting('breeding_modifiers'),
                'inbreeding'        => $this->getBreedingSetting('breeding_inbreeding_trait_rates'),
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

    public function postRollBreeding(Request $request, BreedingService $service, $id, $action) {
        $data = $request->only(['breeding_id', 'remove_stack', 'reroll']);

        if (isset($data['reroll']) && $data['reroll']) {
            $data['reroll'] = true;
        }

        $breeding = Breeding::find($id);

        if ($action === 'approve' && $service->rollBreeding($data, auth()->user())) {
            flash('Breeding rolled successully!')->success();
        } elseif ($action === 'cancel' && $service->cancelBreeding($breeding, auth()->user())) {
            flash('Breeding canceled successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
