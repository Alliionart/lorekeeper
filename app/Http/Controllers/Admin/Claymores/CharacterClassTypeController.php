<?php

namespace App\Http\Controllers\Admin\Claymores;

use App\Http\Controllers\Controller;
use App\Models\Character\CharacterClassType;
use App\Services\Claymore\ClassTypeService;
use Illuminate\Http\Request;

class CharacterClassTypeController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Character Class Type Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of character class types.
    |
    */

    /**
     * Shows the character class index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.claymores.classes.class_types', [
            'types' => CharacterClassType::orderBy('name', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create character class page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateClassType() {
        return view('admin.claymores.classes.create_edit_type', [
            'type'     => new CharacterClassType,
            'types'    => ['' => 'None'] + CharacterClassType::pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit character class page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditClassType($id) {
        $type = CharacterClassType::find($id);
        if (!$type) {
            abort(404);
        }

        return view('admin.claymores.classes.create_edit_type', [
            'type'     => $type,
            'types'    => CharacterClassType::pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a character class.
     *
     * @param App\Services\ClassTypeService $service
     * @param int|null                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditClassType(Request $request, ClassTypeService $service, $id = null) {
        $id ? $request->validate(CharacterClassType::$updateRules) : $request->validate(CharacterClassType::$createRules);
        $data = $request->only([
            'name', 'description', 'parent_type_id',
        ]);
        if ($id && $service->updateClassType(CharacterClassType::find($id), $data)) {
            flash('Type updated successfully.')->success();
        } elseif (!$id && $type = $service->createClassType($data)) {
            flash('Type created successfully.')->success();

            return redirect()->to('admin/character-classes/types/edit/'.$type->id);
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
    public function getDeleteClassType($id) {
        $type = CharacterClassType::find($id);

        return view('admin.claymores.classes._delete_character_class', [
            'type' => $type,
        ]);
    }

    /**
     * Deletes a character class.
     *
     * @param App\Services\ClassTypeService $service
     * @param int                           $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteClassType(Request $request, ClassTypeService $service, $id) {
        if ($id && $service->deleteClassType(CharacterClassType::find($id))) {
            flash('Type deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/character-classes');
    }
}
