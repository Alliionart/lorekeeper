<?php

namespace App\Http\Controllers\Admin\Claymores;

use App\Http\Controllers\Controller;
use App\Models\Character\CharacterClass;
use App\Models\Character\CharacterClassType;
use App\Models\Claymore\Ability;
use App\Services\Claymore\CharacterClassService;
use Illuminate\Http\Request;

class CharacterClassController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Character Class Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of character class.
    |
    */

    /**
     * Shows the character class index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.claymores.classes.character_class', [
            'class' => CharacterClass::orderBy('name', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create character class page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateCharacterClass() {
        return view('admin.claymores.classes.create_edit_character_class', [
            'class'     => new CharacterClass,
            'classes'   => [0 => 'No Parent'] + CharacterClass::pluck('name', 'id')->toArray(),
            'class_types'   => [0 => 'None'] + CharacterClassType::pluck('name', 'id')->toArray(),
            'abilities' => [0 => 'None'] + Ability::pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit character class page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCharacterClass($id) {
        $class = CharacterClass::find($id);
        if (!$class) {
            abort(404);
        }
        $choices = $class->ability_choice ? json_decode($class->ability_choice) : null;

        return view('admin.claymores.classes.create_edit_character_class', [
            'class'     => $class,
            'classes'   => [0 => 'None'] + CharacterClass::pluck('name', 'id')->toArray(),
            'class_types'   => [0 => 'None'] + CharacterClassType::pluck('name', 'id')->toArray(),
            'abilities' => [0 => 'None'] + Ability::pluck('name', 'id')->toArray(),
            'c_ability' => $class->ability_id ?? $choices ?? null,
        ]);
    }

    /**
     * Creates or edits a character class.
     *
     * @param App\Services\CharacterClassService $service
     * @param int|null                           $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditCharacterClass(Request $request, CharacterClassService $service, $id = null) {
        $id ? $request->validate(CharacterClass::$updateRules) : $request->validate(CharacterClass::$createRules);
        $data = $request->only([
            'code', 'name', 'description', 'image', 'remove_image', 'masterlist_sub_id', 'is_visible', 'abilities', 'class_type', 'class_subtype'
        ]);
        if ($id && $service->updateCharacterClass(CharacterClass::find($id), $data)) {
            flash('Class updated successfully.')->success();
        } elseif (!$id && $class = $service->createCharacterClass($data)) {
            flash('Class created successfully.')->success();

            return redirect()->to('admin/character-classes/edit/'.$class->id);
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
    public function getDeleteCharacterClass($id) {
        $class = CharacterClass::find($id);

        return view('admin.claymores.classes._delete_character_class', [
            'class' => $class,
        ]);
    }

    /**
     * Deletes a character class.
     *
     * @param App\Services\CharacterClassService $service
     * @param int                                $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteCharacterClass(Request $request, CharacterClassService $service, $id) {
        if ($id && $service->deleteCharacterClass(CharacterClass::find($id))) {
            flash('Class deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/character-classes');
    }
}
