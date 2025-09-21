<?php

namespace App\Http\Controllers;

use App\Models\Base\Base;
use App\Models\Character\BreedingPermission;
use App\Models\Character\Character;
use App\Models\Character\CharacterImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BreedingController extends Controller {
    public function getBreedingFormEntry() {
        $permissions = BreedingPermission::where('recipient_id', Auth::user()->id);

        return view('breeding.breeding_form', [
            'user'        => Auth::user()->id,
            'permissions' => $this->getUserBreedingEntries($permissions->get()),
        ]);
    }

    public function getUserBreedingEntries($permissions) {
        $select = [
            0 => 'Select a slot',
        ];
        foreach ($permissions as $permission) {
            $character = Character::find($permission->character_id);
            $select[$permission->id] = $permission->type.': '.$character->fullName.' ('.($permission->created_at)->format('M j, Y').')';
        }

        return $select;
    }

    public function getBreedingCharacter(Request $request) {
        $perm_id = $request->input('permission_id');
        $slot_id = $request->input('slot_id');
        $permission = BreedingPermission::find($perm_id);
        if (!$permission) {
            return response()->json(['error' => 'Permission not found'], 404);
        }
        $character = Character::find($permission->character_id);
        $characterImage = CharacterImage::where('character_id', $character->id)->where('is_visible', 1)->first();

        return response()->json([
            'permission' => $permission,
            'character'  => [
                'name'  => $character->fullName,
                'id'    => $character->id,
                'image' => $characterImage->getThumbnailUrlAttribute(),
            ],
            'character_image' => $characterImage,
            'slot_id'         => $slot_id,
        ]);
    }

    // public function getBasePage(Request $request) {
    //     return view('designhub.basespage', [
    //         'bases'      => Base::where('is_visible', 1)->get(),
    //     ]);
    // }
}
