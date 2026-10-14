<?php

namespace App\Http\Controllers;

use App\Models\Base\Base;
use App\Models\Breeding\Breeding;
use App\Models\Character\BreedingPermission;
use App\Models\Character\Character;
use App\Models\Character\CharacterImage;
use App\Models\Item\Item;
use App\Models\Item\ItemTag;
use App\Models\SitePage;
use App\Models\Species\Subtype;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Services\BreedingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Settings;

class BreedingController extends Controller {
    public function getBreedingFormEntry() {
        if (!Auth::check() && !Auth::user()) {
            abort(404);
        }

        $user = Auth::user();
        $permissions = BreedingPermission::where('recipient_id', $user->id)->where('is_used', 0)->where('quantity', '>', 0);

        // Get the applicable starter tokens from the user's inventory
        $token_rules = ItemTag::where('tag', 'starter_token')->where('is_active', 1)->pluck('item_id')->toArray();
        $tokens = UserItem::where('user_id', $user->id)
            ->whereIn('item_id', $token_rules)
            ->where('count', '>', 0)
            ->join('items', 'user_items.item_id', '=', 'items.id') // Join with items table
            ->pluck('items.name', 'user_items.item_id') // Pluck name as value and item_id as key
            ->toArray();

        // Get users own characters with available breeding slots

        $own_characters = Character::where('user_id', $user->id)
            ->where('is_myo_slot', 0)
            ->get();
        $filtered_own_characters = $own_characters->filter(function ($character) {
            return $character->available_breeding_permissions > 0;
        })->mapWithKeys(function ($character) {
            return [$character->id => $character->fullName];
        })->toArray();

        $breeding_item_category = Settings::get('breeding_item_category_id');
        $subtype_choices = json_decode(Settings::get('breeding_subtype_rates'));

        return view('breeding.breeding_form', [
            'queue_status'   => Settings::get('breeding_queue_status'),
            'user'           => $user->id,
            'permissions'    => $this->getUserBreedingEntries($permissions->get()),
            'tokens'         => [0 => 'Select a token'] + $tokens,
            'own_characters' => [0 => 'Select a character'] + $filtered_own_characters,
            'breeding_items' => Item::where('item_category_id', $breeding_item_category)->get(),
            'user_items'     => UserItem::where('user_id', $user->id)
                ->whereIn('item_id', Item::where('item_category_id', $breeding_item_category)->pluck('id')->toArray())
                ->select('item_id', DB::raw('COALESCE(SUM(`count`),0) - COALESCE(SUM(`trade_count`),0) - COALESCE(SUM(`update_count`),0) - COALESCE(SUM(`submission_count`),0) - COALESCE(SUM(`breeding_count`),0) as available'))
                ->groupBy('item_id')
                ->having('available', '>', 0)
                ->pluck('available', 'item_id')
                ->map(function ($v) {
                    return (int) $v;
                })
                ->toArray(),
            'mod_items'     => json_decode(Settings::get('breeding_modifiers')) ?? null,
            'counts'        => $user->breedCount,
            'users'         => [0 => 'Select a user'] + User::where('is_banned', '!=', 1)->where('id', '!=', $user->id)->where('is_deactivated', '!=', 1)->orderBy('name')->pluck('name', 'id')->toArray(),
            'maxSlots'      => [
                'full'          => (int) Settings::get('breeding_max_full_slots') ?? 0,
                'split'         => (int) Settings::get('breeding_max_split_slots') ?? 0,
            ],
            'bases'           => ['' => 'Select color'] + Base::where('is_visible', 1)->orderBy('name')->pluck('name', 'id')->toArray(),
            'subtypes'        => Subtype::where('is_visible', 1)->pluck('name', 'id')->toArray(),
            'subtype_choices' => $subtype_choices,
        ]);
    }

    /**
     * Posts the initial breeding request.
     */
    public function postBreedingFormEntry(Request $request, BreedingService $service) {
        $data = $request->only(['parents', 'breeding_items', 'type', 'split_user']);

        if ($breeding = $service->createBreeding($data, Auth::user())) {
            flash('Breeding request created: '.$breeding->getDisplayNameAttribute())->success();

            return redirect()->to($breeding->getUrlAttribute());
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Get a list of breeding entries for the user.
     *
     * @param \Illuminate\Database\Eloquent\Collection $permissions
     */
    public function getUserBreedingEntries($permissions) {
        $select = [
            0 => 'Select a permission',
        ];
        foreach ($permissions as $permission) {
            $character = Character::find($permission->character_id);
            $select[$permission->id] = $permission->type.': '.$character->fullName.' - ['.$permission->quantity.'/'.$permission->full_quantity.'] ('.($permission->created_at)->format('M j, Y').')';
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
        $characters = ItemTag::where('item_id', $token)->where('tag', 'starter_token')->first();
        $characters = Character::whereIn('id', $characters->data)
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
        $is_starter = false;
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

        if ($character->category->name === 'Starter') {
            $is_starter = true;
        }

        return response()->json([
            'character'  => [
                'is_starter'    => $is_starter,
                'name'          => $character->fullName,
                'id'            => $character->id,
                'sex'           => $character->sex,
                'species'       => $characterImage->species->displayName,
                'rawspecies'    => $characterImage->species->id,
                'subtype'       => $characterImage->subtype->displayName,
                'rawsubtype'    => $characterImage->subtype->id,
                'image'         => $characterImage->getThumbnailUrlAttribute(),
                'markings'      => strip_tags($character->getMarkingLinkedArray($markings)),
                'traits'        => $trait_display,
                'lineage'       => $this->getViableLineageIds($character),
                'base'          => $character->base,
                'url'           => $character->getUrlAttribute(),
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
                'name'          => $character->fullName,
                'id'            => $character->id,
                'sex'           => $character->sex,
                'species'       => $characterImage->species->displayName,
                'rawspecies'    => $characterImage->species->id,
                'subtype'       => $characterImage->subtype->displayName,
                'rawsubtype'    => $characterImage->subtype->id,
                'image'         => $characterImage->getThumbnailUrlAttribute(),
                'markings'      => strip_tags($character->getMarkingLinkedArray($markings)),
                'traits'        => $trait_display,
                'lineage'       => $this->getViableLineageIds($character),
                'base'          => $character->base,
                'url'           => $character->getUrlAttribute(),
            ],
            'slot_id'         => $slot_id,
        ]);
    }

    public function getViableLineageIds($character) {
        if (!$character->lineage) {
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
            'page' => SitePage::where('key', 'breeding')->first() ?? null,
        ]);
    }

    /**
     * Shows the cancel breeding modal.
     *
     * @param string $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCancelBreeding($id) {
        $breeding = Breeding::find($id);

        if (!$breeding || !Auth::check()) {
            abort(404);
        }

        return view('breeding._cancel', [
            'breeding' => $breeding,
        ]);
    }

    /**
     * Shows the new breeding split accept modal.
     *
     * @param string $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getApproveSplit($id) {
        $breeding = Breeding::find($id);
        if (!$breeding || !Auth::check() || Auth::user()->id !== $breeding->split_user_id) {
            abort(404);
        }

        return view('breeding.split._approve', [
            'breeding' => $breeding,
        ]);
    }

    /**
     * Shows the new breeding split accept modal.
     *
     * @param string $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRejectSplit($id) {
        $breeding = Breeding::find($id);
        if (!$breeding || !Auth::check() || Auth::user()->id !== $breeding->split_user_id) {
            abort(404);
        }

        return view('breeding.split._reject', [
            'breeding' => $breeding,
        ]);
    }

    /**
     * Shows the new breeding split confirm modal.
     *
     * @param string $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getConfirmSplit($id) {
        $breeding = Breeding::find($id);
        if (!$breeding || !Auth::check() || Auth::user()->id !== $breeding->split_user_id) {
            abort(404);
        }

        return view('breeding.split._confirm', [
            'breeding' => $breeding,
        ]);
    }

    /**
     * Cancels the breeding request.
     *
     * @param App\Services\BreedingService $service
     * @param string                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCancelBreeding(Request $request, BreedingService $service, $id) {
        $breeding = Breeding::find($id);

        if (!$breeding || !Auth::check()) {
            abort(404);
        }

        if ($service->cancelBreeding($breeding, Auth::user())) {
            flash('Breeding was canceled. You can find it under your breeding drafts.')->error();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Marks the split breeding approved by the split user.
     *
     * @param App\Services\BreedingService $service
     * @param string                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postApproveSplit(Request $request, BreedingService $service, $id) {
        $breeding = Breeding::find($id);

        if (!$breeding || !Auth::check()) {
            abort(404);
        }

        if ($service->approveSplit($breeding, Auth::user())) {
            flash('Split breeding approved successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Marks the split breeding approved by the split user.
     *
     * @param App\Services\BreedingService $service
     * @param string                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRejectSplit(Request $request, BreedingService $service, $id) {
        $breeding = Breeding::find($id);

        if (!$breeding || !Auth::check()) {
            abort(404);
        }

        if ($service->rejectSplit($breeding, Auth::user())) {
            flash('Split breeding was rejected.')->error();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Marks the split breeding confirmed by the split user.
     *
     * @param App\Services\BreedingService $service
     * @param string                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postConfirmSplit(Request $request, BreedingService $service, $id) {
        $breeding = Breeding::find($id);

        if (!$breeding || !Auth::check()) {
            abort(404);
        }

        if ($service->confirmSplit($breeding, Auth::user())) {
            flash('Split breeding was confirmed.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the convert pup modal for both Surrender/Convert.
     *
     * @param string $id
     * @param string $pup
     * @param string $type
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getConvertPup($id, $pup, $type) {
        $breeding = Breeding::find($id);

        if (!$breeding || !Auth::check()) {
            abort(404);
        }

        return view('breeding._convert', [
            'breeding'  => $breeding,
            'type'      => $type == 'convert' ? true : false,
            'pup'       => $pup,
        ]);
    }

    /**
     * Shows the modal for releasing a pup to the wild.
     *
     * @param string $id
     * @param string $pup
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getReleasePup($id, $pup) {
        $breeding = Breeding::find($id);

        if (!$breeding || !Auth::check()) {
            abort(404);
        }

        return view('breeding._release', [
            'breeding'  => $breeding,
            'pup'       => $pup,
        ]);
    }

    /**
     * Converts the pup to a character or sends it to the adoption center.
     *
     * @param App\Services\BreedingService $service
     * @param string                       $id
     * @param string                       $pup
     * @param string                       $type
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postConvertPup(Request $request, BreedingService $service, $id, $pup, $type) {
        $user_id = $request->only(['user_id']);
        $breeding = Breeding::find($id);

        if (!$breeding || !Auth::check()) {
            abort(404);
        }

        $newOwner = $type === 'convert' ? $breeding->user_id : false;
        $surrender = $type === 'convert' ? false : true;
        $exchange = $request->input('exchange_type');

        if ($user_id && $breeding->split_user_id && !$surrender) {
            $newOwner = $user_id['user_id'];
        }

        if ($surrender && config('lorekeeper.breeding.surrender_extension_exists')) {
            flash('Surrender extension is not available.')->error();

            return redirect()->back();
        }

        if ($character = $service->convertPup($breeding, Auth::user(), $pup, $newOwner, $exchange)) {
            flash($surrender ? 'Pup was successfully sent to the Adoption Center: '.$character->displayName : 'Pup was successfully converted to: '.$character->displayName)->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Releases the pup.
     *
     * @param App\Services\BreedingService $service
     * @param string                       $id
     * @param string                       $pup
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postReleasePup(Request $request, BreedingService $service, $id, $pup) {
        $breeding = Breeding::find($id);

        if (!$breeding || !Auth::check()) {
            abort(404);
        }

        if ($service->releasePup($breeding, Auth::user(), $pup)) {
            flash('Pup was successfully released.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /** ----------------------------------------
     * EDIT DRAFTED BREEDINGS
     * ----------------------------------------.
     *
     * @param mixed $id
     */

    /**
     * Shows the breeding form with pre-filled draft information.
     *
     * @param string $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditBreeding($id) {
        if (!Auth::check() && !Auth::user()) {
            abort(404);
        }

        $user = Auth::user();
        $permissions = BreedingPermission::where('recipient_id', $user->id)->where('is_used', 0)->where('quantity', '>', 0);
        $breeding = Breeding::find($id);

        if (!$breeding || $breeding->status !== 'Draft') {
            abort(404);
        }

        if ($breeding->user->id !== $user->id) {
            abort(404);
        }

        // Get the applicable starter tokens from the user's inventory
        $token_rules = ItemTag::where('tag', 'starter_token')->where('is_active', 1)->pluck('item_id')->toArray();
        $tokens = UserItem::where('user_id', $user->id)
            ->whereIn('item_id', $token_rules)
            ->where('count', '>', 0)
            ->join('items', 'user_items.item_id', '=', 'items.id') // Join with items table
            ->pluck('items.name', 'user_items.item_id') // Pluck name as value and item_id as key
            ->toArray();

        // Get users own characters with available breeding slots
        $own_characters = Character::where('user_id', $user->id)->where('is_myo_slot', 0)->get();
        $filtered_own_characters = $own_characters->filter(function ($character) {
            return $character->available_breeding_permissions > 0;
        })->mapWithKeys(function ($character) {
            return [$character->id => $character->fullName];
        })->toArray();

        $breeding_item_category = Settings::get('breeding_item_category_id');
        $subtype_choices = json_decode(Settings::get('breeding_subtype_rates'));

        return view('breeding.breeding_form', [
            'queue_status'   => Settings::get('breeding_queue_status'),
            'breeding'       => $breeding,
            'user'           => $user->id,
            'permissions'    => $this->getUserBreedingEntries($permissions->get()),
            'tokens'         => [0 => 'Select a token'] + $tokens,
            'own_characters' => [0 => 'Select a character'] + $filtered_own_characters,
            'breeding_items' => Item::where('item_category_id', $breeding_item_category)->get(),
            'user_items'     => UserItem::where('user_id', $user->id)
                ->whereIn('item_id', Item::where('item_category_id', $breeding_item_category)->pluck('id')->toArray())
                ->select('item_id', DB::raw('COALESCE(SUM(`count`),0) - COALESCE(SUM(`trade_count`),0) - COALESCE(SUM(`update_count`),0) - COALESCE(SUM(`submission_count`),0) - COALESCE(SUM(`breeding_count`),0) as available'))
                ->groupBy('item_id')
                ->having('available', '>', 0)
                ->pluck('available', 'item_id')
                ->map(function ($v) {
                    return (int) $v;
                })
                ->toArray(),
            'mod_items'     => json_decode(Settings::get('breeding_modifiers')) ?? null,
            'counts'        => $user->breedCount,
            'users'         => [0 => 'Select a user'] + User::where('is_banned', '!=', 1)->where('id', '!=', $user->id)->where('is_deactivated', '!=', 1)->orderBy('name')->pluck('name', 'id')->toArray(),
            'maxSlots'      => [
                'full'          => (int) Settings::get('breeding_max_full_slots') ?? 0,
                'split'         => (int) Settings::get('breeding_max_split_slots') ?? 0,
            ],
            'bases'           => ['' => 'Select color'] + Base::where('is_visible', 1)->orderBy('name')->pluck('name', 'id')->toArray(),
            'subtypes'        => Subtype::where('is_visible', 1)->pluck('name', 'id')->toArray(),
            'subtype_choices' => $subtype_choices,
        ]);
    }

    /**
     * Edits an existing breeding.
     *
     * @param App\Services\BreedingService $service
     * @param string                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditBreeding(Request $request, BreedingService $service, $id) {
        $data = $request->only(['parents', 'breeding_items', 'type', 'split_user']);
        $breeding = Breeding::find($id);

        if (!$breeding || !Auth::check()) {
            abort(404);
        }

        if ($service->editBreeding($breeding, $data, Auth::user())) {
            flash('Breeding edited and resubmitted: '.$breeding->getDisplayNameAttribute())->success();

            return redirect()->to($breeding->getUrlAttribute());
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
