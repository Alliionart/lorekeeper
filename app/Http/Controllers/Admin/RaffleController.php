<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Raffle\Raffle;
use App\Models\Raffle\RaffleGroup;
use App\Models\Raffle\RaffleTicket;
use App\Models\User\User;
use App\Services\RaffleManager;
use App\Services\RaffleService;
use Illuminate\Http\Request;
use Auth;

class RaffleController extends Controller {
    /**
     * Shows the raffle index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRaffleIndex(Request $request) {
        $raffles = Raffle::query();
        if ($request->get('is_active')) {
            $raffles->where('is_active', $request->get('is_active'));
        } else {
            $raffles->where('is_active', '!=', 2);
        }
        $raffles = $raffles->orderBy('group_id')->orderBy('order');

        return view('admin.raffle.index', [
            'raffles' => $raffles->get(),
            'groups'  => RaffleGroup::whereIn('id', $raffles->pluck('group_id')->toArray())->get()->keyBy('id'),
        ]);
    }

    /**
     * Shows the create/edit raffle modal.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateEditRaffle($id = null) {
        $raffle = null;
        if ($id) {
            $raffle = Raffle::find($id);
            if (!$raffle) {
                abort(404);
            }
        } else {
            $raffle = new Raffle;
        }

        $rewardOptions = ['all' => 'Entire raffle'];
        foreach ($raffle->rewards as $reward) {
            $rewardName = $reward->reward->name ?? 'Deleted reward';
            $label = ($reward->position ? 'Position '.$reward->position.' - ' : '').$rewardName.' ('.$reward->rewardable_type.')';
            $rewardOptions[$reward->id] = $label;
        }

        return view('admin.raffle._raffle_create_edit', [
            'raffle' => $raffle,
            'groups' => [0 => 'No group'] + RaffleGroup::where('is_active', '<', 2)->pluck('name', 'id')->toArray(),
            'rewardOptions' => $rewardOptions,
            'users' => User::visible()->orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a raffle.
     *
     * @param App\Services\RaffleService $service
     * @param int|null                   $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditRaffle(Request $request, RaffleService $service, $id = null) {
        $request->validate([
            'excluded_winners' => 'sometimes|array',
            'excluded_winners.*.reward_id' => 'nullable|string',
            'excluded_winners.*.user_ids' => 'nullable|array|max:20',
            'excluded_winners.*.user_ids.*' => 'integer|distinct|exists:users,id',
        ]);
        $data = $request->only(['name', 'is_active', 'is_admin_only', 'winner_count', 'group_id', 'order', 'ticket_cap', 'rewards_type', 'excluded_winners']);
        $data['excluded_winners'] = $request->input('excluded_winners', []);
        $raffle = null;
        if (!$id) {
            $raffle = $service->createRaffle($data);
        } elseif ($id) {
            $raffle = $service->updateRaffle($data, Raffle::find($id));
        }
        if ($raffle) {
            flash('Raffle '.($id ? 'updated' : 'created').' successfully!')->success();

            return redirect()->back();
        } else {
            flash('Couldn\'t create raffle.')->error();

            return redirect()->back()->withInput();
        }
    }

    /**
     * Shows the create/edit raffle group modal.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateEditRaffleGroup($id = null) {
        $group = null;
        if ($id) {
            $group = RaffleGroup::find($id);
            if (!$group) {
                abort(404);
            }
        } else {
            $group = new RaffleGroup;
        }

        return view('admin.raffle._group_create_edit', [
            'group' => $group,
        ]);
    }

    /**
     * Creates or edits a raffle group.
     *
     * @param App\Services\RaffleService $service
     * @param int|null                   $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditRaffleGroup(Request $request, RaffleService $service, $id = null) {
        $data = $request->only(['name', 'is_active']);
        $group = null;
        if (!$id) {
            $group = $service->createRaffleGroup($data);
        } elseif ($id) {
            $group = $service->updateRaffleGroup($data, RaffleGroup::find($id));
        }
        if ($group) {
            flash('Raffle group '.($id ? 'updated' : 'created').' successfully!')->success();

            return redirect()->back();
        } else {
            flash('Couldn\'t create raffle group.')->error();

            return redirect()->back()->withInput();
        }
    }

    /**
     * Shows the ticket index of a raffle.
     *
     * @param \|int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRaffleTickets(Request $request, $id) {
        $raffle = Raffle::find($id);
        if (!$raffle) {
            abort(404);
        }

        return view('admin.raffle.ticket_index', [
            'raffle'  => $raffle,
            'tickets' => $raffle->tickets()->orderBy('id')->paginate(200),
            'users'   => User::visible()->orderBy('name')->pluck('name', 'id')->toArray(),
            'page'    => $request->get('page') ? $request->get('page') - 1 : 0,
        ]);
    }

    /**
     * Creates raffle tickets for a raffle.
     *
     * @param App\Services\RaffleManager $service
     * @param int                        $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateRaffleTickets(Request $request, RaffleManager $service, $id) {
        $request->validate(RaffleTicket::$createRules);
        $data = $request->only('user_id', 'alias', 'ticket_count');
        if ($count = $service->addTickets(Raffle::find($id), $data)) {
            flash($count.' tickets added!')->success();

            return redirect()->back();
        } else {
            flash('Couldn\'t add tickets.')->error();

            return redirect()->back()->withInput();
        }
    }

    /**
     * Deletes a raffle ticket.
     *
     * @param App\Services\RaffleManager $service
     * @param int                        $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteRaffleTicket(Request $request, RaffleManager $service, $id) {
        if ($service->removeTicket(RaffleTicket::find($id))) {
            flash('Ticket removed.')->success();

            return redirect()->back();
        } else {
            flash('Couldn\'t remove ticket.')->error();

            return redirect()->back()->withInput();
        }
    }

    /**
     * Shows the confirmation modal for rolling a raffle.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRollRaffle($id) {
        $raffle = Raffle::find($id);
        if (!$raffle) {
            abort(404);
        }

        return view('admin.raffle._raffle_roll', [
            'raffle' => $raffle,
        ]);
    }

    /**
     * Rolls a raffle.
     *
     * @param App\Services\RaffleManager $service
     * @param int                        $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRollRaffle(RaffleManager $service, $id) {
        if ($service->rollRaffle(Raffle::find($id))) {
            flash('Winners rolled!')->success();

            return redirect()->back();
        } else {
            flash('Error in rolling winners.')->error();

            return redirect()->back()->withInput();
        }
    }

    /**
     * Shows the confirmation modal for rolling a raffle group.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRollRaffleGroup($id) {
        $group = RaffleGroup::find($id);
        if (!$group) {
            abort(404);
        }

        return view('admin.raffle._group_roll', [
            'group' => $group,
        ]);
    }

    /**
     * Rolls a raffle group.
     *
     * @param App\Services\RaffleManager $service
     * @param int                        $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRollRaffleGroup(RaffleManager $service, $id) {
        if ($service->rollRaffleGroup(RaffleGroup::find($id))) {
            flash('Winners rolled!')->success();

            return redirect()->back();
        } else {
            flash('Error in rolling winners.')->error();

            return redirect()->back()->withInput();
        }
    }

    /**
     * Shows the page to manage raffle rewards.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRaffleRewards($id = null) {
        $raffle = Raffle::find($id);

        if ( !$raffle ) {
            abort(404);
        }

        return view('admin.raffle.create_edit_rewards', [
            'raffle' => $raffle,
            'rewards' => $raffle->rewards ?? [],
        ]);
    }

    /**
     * Creates or edits a raffle.
     *
     * @param App\Services\RaffleService $service
     * @param int|null                   $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRaffleRewards(Request $request, RaffleService $service, $id = null) {
        $data = $request->all();
        
        if ($service->editRaffleRewards(Raffle::find($id), $data, Auth::user())) {
            flash('Raffle rewards updated.')->success();

            return redirect()->back();
        } else {
            flash('Error updating raffle rewards.')->error();

            return redirect()->back()->withInput();
        }
    }
}
