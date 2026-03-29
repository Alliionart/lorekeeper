<?php

namespace App\Http\Controllers\Admin\Claymores;

use App\Http\Controllers\Controller;
use App\Models\Claymore\Ability;
use App\Models\Claymore\AbilityType;
use App\Models\Stat\Stat;
use App\Models\Status\StatusEffect;
use App\Services\Claymore\AbilityService;
use Illuminate\Http\Request;

class AbilityController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Ability Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of abilities.
    |
    */

    /**
     * Shows the character class index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.claymores.abilities.ability', [
            'abilities' => Ability::orderBy('name', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create ability page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateAbility() {
        return view('admin.claymores.abilities.create_edit_ability', [
            'ability'           => new Ability,
            'effects'           => [],
            'types'             => [0 => 'None'] + AbilityType::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
            'stats'             => Stat::pluck('name', 'id')->toArray(),
            'status_effects'    => ['' => 'None'] + StatusEffect::pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit character class page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditAbility($id) {
        $ability = Ability::find($id);
        if (!$ability) {
            abort(404);
        }

        return view('admin.claymores.abilities.create_edit_ability', [
            'ability'           => $ability,
            'effects'           => $ability->data,
            'types'             => [0 => 'None'] + AbilityType::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
            'stats'             => Stat::pluck('name', 'id')->toArray(),
            'status_effects'    => ['' => 'None'] + StatusEffect::pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a character class.
     *
     * @param App\Services\AbilityService $service
     * @param int|null                    $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditAbility(Request $request, AbilityService $service, $id = null) {
        $id ? $request->validate(Ability::$updateRules) : $request->validate(Ability::$createRules);
        $data = $request->except(['_token']);

        if ($id && $service->updateAbility(Ability::find($id), $data)) {
            flash('Ability updated successfully.')->success();
        } elseif (!$id && $ability = $service->createAbility($data)) {
            flash('Ability created successfully.')->success();

            return redirect()->to('admin/abilities/edit/'.$ability->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the character class deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteAbility($id) {
        $ability = Ability::find($id);

        return view('admin.claymores.abilities._delete_ability', [
            'ability' => $ability,
        ]);
    }

    /**
     * Deletes a character class.
     *
     * @param App\Services\AbilityService $service
     * @param int                         $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteAbility(Request $request, AbilityService $service, $id) {
        if ($id && $service->deleteAbility(Ability::find($id))) {
            flash('Class deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/character-abilities');
    }

    /* -----------------------------------
        ABILITY TYPES
    ------------------------------------- */

    /**
     * Shows the character class index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTypeIndex() {
        return view('admin.claymores.abilities.ability_types', [
            'types' => AbilityType::orderBy('name', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create ability page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateAbilityType() {
        return view('admin.claymores.abilities.create_edit_ability_type', [
            'ability_type' => new AbilityType,
        ]);
    }
}
