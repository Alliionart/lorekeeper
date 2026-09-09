<?php

namespace App\Http\Controllers\Guilds;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Character\CharacterItem;
use App\Models\Guild\Guild;
use App\Models\Guild\GuildItem;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Queue\QueueSubmission;
use App\Models\Submission\Submission;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Services\InventoryManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Inventory Controller
    |--------------------------------------------------------------------------
    |
    | Handles inventory management for guilds.
    |
    */

    /**
     * Shows the inventory stack modal, for guilds.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildStack(Request $request, $id) {
        $first_instance = GuildItem::withTrashed()->where('id', $id)->first();
        $stack = GuildItem::where([['guild_id', $first_instance->guild_id], ['item_id', $first_instance->item_id], ['count', '>', 0]])->get();
        $item = Item::where('id', $first_instance->item_id)->first();

        $guild = $first_instance->guild;
        isset($stack->first()->guild->id) ?
        $ownerId = $stack->first()->guild->id : null;

        $hasPower = Auth::check() ? Auth::user()->hasPower('edit_inventories') : false;
        $readOnly = $request->get('read_only') ?: ((Auth::check() && $first_instance && (isset($ownerId) == true || $hasPower == true)) ? 0 : 1);

        $members = $guild->members()
                    ->with('user')
                    ->get()
                    ->mapWithKeys(fn ($m) => [$m->user_id => $m->user->name])
                    ->all();

        $characters = $guild->characters()
                    ->with('character')
                    ->get()
                    ->mapWithKeys(fn ($c) => [$c->character_id => $c->character->fullName])
                    ->all();

        return view('guilds._inventory_stack', [
            'stack'         => $stack,
            'item'          => $item,
            'user'          => Auth::user(),
            'has_power'     => $hasPower,
            'readOnly'      => $readOnly,
            'guild'         => $guild,
            'owner_id'      => $ownerId ?? null,
            'members'       => $members,
            'characters'    => $characters,
            'allowed_users' => null, //Get users who can edit the guild here (Owner and Mods)!
        ]);
    }

    /**
     * Edits the inventory of involved users.
     *
     * @param App\Services\InventoryManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(Request $request, InventoryManager $service) {
        if (!$request->ids) {
            flash('No items selected.')->error();
        }
        if (!$request->quantities) {
            flash('Quantities not set.')->error();
        }

        if ($request->ids && $request->quantities) {
            switch ($request->action) {
                default:
                    flash('Invalid action selected.')->error();
                    break;
                case 'transfer':
                    return $this->postTransfer($request, $service);
                    break;
                case 'delete':
                    return $this->postDelete($request, $service);
                    break;
                case 'characterTransfer':
                    return $this->postTransferToCharacter($request, $service);
                    break;
                case 'resell':
                    return $this->postResell($request, $service);
                    break;
                case 'act':
                    return $this->postAct($request);
                    break;
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the inventory selection widget.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSelector($id) {
        return view('widgets._inventory_select', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Transfers inventory items to another user.
     *
     * @param App\Services\InventoryManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postTransfer(Request $request, InventoryManager $service) {
        $guild = Guild::find($request->route('id'));
        
        if ($service->transferGuildStack($guild, User::visible()->where('id', $request->get('user'))->first(), GuildItem::find($request->get('ids')), $request->get('quantities'), Auth::user())) {
            flash('Item transferred successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Transfers inventory items to a character.
     *
     * @param App\Services\InventoryManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postTransferToCharacter(Request $request, InventoryManager $service) {
        $guild = Guild::find($request->route('id'));

        if ($service->transferCharacterStack($guild, Character::visible()->where('id', $request->get('character_id'))->first(), GuildItem::find($request->get('ids')), $request->get('quantities'), Auth::user())) {
            flash('Item transferred successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Deletes an inventory stack.
     *
     * @param App\Services\InventoryManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postDelete(Request $request, InventoryManager $service) {
        $guild = Guild::find($request->route('id'));

        if ($service->deleteStack($guild, GuildItem::find($request->get('ids')), $request->get('quantities'), Auth::user())) {
            flash('Item deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Sells an inventory stack.
     *
     * @param App\Services\InventoryManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postResell(Request $request, InventoryManager $service) {
        $guild = Guild::find($request->route('id'));

        if ($service->resellStack($guild, GuildItem::find($request->get('ids')), $request->get('quantities'))) {
            flash('Item sold successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Acts on an item based on the item's tag.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postAct(Request $request) {
        $stacks = GuildItem::with('item')->find($request->get('ids'));
        $tag = $request->get('tag');
        $service = $stacks->first()->item->hasTag($tag) ? $stacks->first()->item->tag($tag)->service : null;
        if ($service && $service->act($stacks, Auth::user(), $request->all())) {
            flash('Item used successfully.')->success();
        } elseif (!$stacks->first()->item->hasTag($tag)) {
            flash('Invalid action selected.')->error();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
