<?php

namespace App\Http\Controllers;

use App\Models\Breeding\Breeding;
use App\Models\Character\BreedingPermission;
use App\Models\Character\Character;
use App\Models\Character\CharacterImage;
use App\Models\SitePage;
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
        $markings = $character->getMarkingFinalArray();
        $traits = $characterImage->features()->with('feature.category')->get();
        $trait_display = [];
        foreach ($traits as $feature) {
            $trait_display[$feature->feature->category->displayName] = $feature->feature->displayName;
        }

        return response()->json([
            'permission' => $permission,
            'character'  => [
                'name'      => $character->fullName,
                'id'        => $character->id,
                'species'   => $characterImage->species->displayName,
                'subtype'   => $characterImage->subtype_id,
                'image'     => $characterImage->getThumbnailUrlAttribute(),
                'markings'  => strip_tags($character->getMarkingLinkedArray($markings)),
                'traits'    => $trait_display,
                'lineage'   => $this->getViableLineageIds($character),
            ],
            'slot_id'         => $slot_id,
        ]);
    }

    public function getViableLineageIds($character) {
        $lineage = [
            'sire_id'       => $character->lineage->sire_id,
            'sire_sire_id'  => $character->lineage->sire_sire_id,
            'sire_dam_id'   => $character->lineage->sire_dam_id,
            'dam_id'        => $character->lineage->dam_id,
            'dam_sire_id'   => $character->lineage->dam_sire_id,
            'dam_dam_id'    => $character->lineage->dam_dam_id,
        ];
        $final_ids = [];
        foreach ($lineage as $id) {
            if (!in_array($id, $final_ids) && $id) {
                $final_ids[$id] = Character::find($id)->displayName;
            }
        }

        return $final_ids;
    }

    public function getBreedingPage($slug) {
        $breeding = Breeding::where('id', $slug)->first();

        if (!$breeding) {
            abort(404);
        }

        return view('breeding.breeding_page', [
            'id'       => $slug,
            'breeding' => $breeding,
        ]);
    }

    public function getBreedingIndex() {
        return view('breeding.breeding', [
            'info' => SitePage::where('key', 'breeding')->first() ?? null,
        ]);
    }
}
