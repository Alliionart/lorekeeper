<?php

namespace App\Http\Controllers;

use App\Models\Raffle\Raffle;
use App\Models\Raffle\RaffleGroup;
use App\Services\RaffleManager;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class RaffleController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Raffle Controller
    |--------------------------------------------------------------------------
    |
    | Displays raffles and raffle tickets.
    |
    */

    /**
     * Shows the raffle index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRaffleIndex() {
        $raffles = Raffle::query();
        if (Request::get('view') == 'completed') {
            $raffles->where('is_active', 2);
        } else {
            $raffles->where('is_active', '=', 1);
        }

        if (!Auth::user()->isStaff) {
            $raffles->where('is_admin_only', 0);
        }

        $raffles = $raffles->orderBy('group_id')->orderBy('order');

        return view('raffles.index', [
            'raffles' => $raffles->get(),
            'groups'  => RaffleGroup::whereIn('id', $raffles->pluck('group_id')->toArray())->get()->keyBy('id'),
        ]);
    }

    /**
     * Shows tickets for a given raffle.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRaffleTickets($id) {
        $raffle = Raffle::find($id);
        if (!$raffle || !$raffle->is_active || (!Auth::user()->isStaff && $raffle->is_admin_only)) {
            abort(404);
        }

        $userCount = Auth::check() ? $raffle->tickets()->where('user_id', Auth::user()->id)->count() : 0;
        $count = $raffle->tickets()->count();
        $userOptedOut = Auth::check() && $userCount
            ? ($raffle->tickets()->where('user_id', Auth::user()->id)->first()->opted_out ?? [])
            : [];

        return view('raffles.ticket_index', [
            'raffle'       => $raffle,
            'tickets'      => $raffle->tickets()->with('user')->orderBy('id')->paginate(100),
            'count'        => $count,
            'userCount'    => $userCount,
            'userOptedOut' => collect($userOptedOut)->map(function ($reward) {
                return $reward['rewardable_type'].':'.$reward['rewardable_id'];
            })->all(),
            'page'      => Request::get('page') ? Request::get('page') - 1 : 0,
        ]);
    }

    /**
     * Saves a user's Tiered raffle prize opt-outs.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postUpdateOptOuts(HttpRequest $request, RaffleManager $service, $id) {
        if (!Auth::check()) {
            abort(403);
        }

        $request->validate([
            'opted_out'   => 'sometimes|array',
            'opted_out.*' => 'string',
        ]);

        $raffle = Raffle::findOrFail($id);
        if ($service->updateOptedOutRewards($raffle, Auth::user(), $request->input('opted_out', []))) {
            flash('Prize preferences updated.')->success();
        } else {
            flash('Could not update prize preferences.')->error();
        }

        return redirect()->back();
    }
}
