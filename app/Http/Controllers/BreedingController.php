<?php

namespace App\Http\Controllers;

use App\Models\Breeding\Breeding;
use App\Models\Character\BreedingPermission;
use App\Models\Character\Character;
use App\Models\Character\CharacterImage;
use App\Models\Item\ItemTag;
use App\Models\User\UserItem;
use App\Models\SitePage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BreedingController extends Controller {
    public function getBreedingFormEntry() {
        $permissions = BreedingPermission::where('recipient_id', Auth::user()->id);

        // Get the applicable starter tokens from the user's inventory
        $token_rules = ItemTag::where('tag', 'startertoken')->where('is_active', 1)->pluck('item_id')->toArray();
        $tokens = UserItem::where('user_id', Auth::user()->id)
            ->whereIn('item_id', $token_rules)
            ->where('count', '>', 0)
            ->join('items', 'user_items.item_id', '=', 'items.id') // Join with items table
            ->pluck('items.name', 'user_items.item_id') // Pluck name as value and item_id as key
            ->toArray();

        // Get users own characters with available breeding slots
        $own_characters = Character::where('user_id', Auth::user()->id)->where('is_myo_slot', 0)->get();
        $filtered_own_characters = $own_characters->filter(function ($character) {
            return $character->available_breeding_permissions > 0;
        })->mapWithKeys(function ($character) {
            return [$character->id => $character->fullName];
        })->toArray();

        return view('breeding.breeding_form', [
            'user'          => Auth::user()->id,
            'permissions'   => $this->getUserBreedingEntries($permissions->get()),
            'tokens'        => [0 => 'Select a token'] + $tokens,
            'own_characters' => [0 => 'Select a character'] + $filtered_own_characters,
        ]);
    }

    /**
     * Get a list of breeding entries for the user.
     * 
     * @param  \Illuminate\Database\Eloquent\Collection  $permissions
     */
    public function getUserBreedingEntries($permissions) {
        $select = [
            0 => 'Select a permission',
        ];
        foreach ($permissions as $permission) {
            $character = Character::find($permission->character_id);
            $select[$permission->id] = $permission->type.': '.$character->fullName.' ('.($permission->created_at)->format('M j, Y').')';
        }

        return $select;
    }

    /**
     * Get a list of available starters for the token.
     */
    public function getStartersByToken(Request $request) {
        $token = $request->input('token');
        $select = [
            0 => 'Select a starter',
        ];
        $character_categories = ItemTag::where('item_id', $token)->where('tag', 'startertoken')->first();
        $characters = Character::whereIn('character_category_id', $character_categories->data)
            ->where('is_myo_slot', 0)
            ->get();

        foreach ($characters as $character) {
            $select[$character->id] = $character->fullName;
        }

        return response()->json([
            'starters'  => $select,
        ]);
    }

    /**
     * Get the desired character by ID.
     */
    public function getCharacterById(Request $request) {
        $char_id = $request->input('character_id');
        $character = Character::find($char_id);
        if (!$character) {
            return response()->json(['error' => 'Character not found'], 404);
        }
        $characterImage = CharacterImage::where('character_id', $character->id)->where('is_visible', 1)->first();
        $markings = $character->getMarkingFinalArray();
        $traits = $characterImage->features()->with('feature.category')->get();
        $trait_display = [];
        foreach ($traits as $feature) {
            $trait_display[$feature->feature->category->displayName] = $feature->feature->displayName;
        }

        return response()->json([
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
        ]);
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
        if(!$character->lineage) {
            return [];
        }
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
