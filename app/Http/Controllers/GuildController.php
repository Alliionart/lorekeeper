<?php

namespace App\Http\Controllers;

use App\Models\Guild\Guild;
use Illuminate\Http\Request;

class GuildController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Queues Controller
    |--------------------------------------------------------------------------
    |
    | Displays information about queues as entered in the admin panel.
    | Pages displayed by this controller form the Queues section of the site.
    |
    */

    /**
     * Shows the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildIndex(Request $request) {
        $query = Guild::query();
        $sort = $request->only(['sort']);

        if ($request->get('name')) {
            $query->where(function ($query) use ($request) {
                $query->where('guilds.name', 'LIKE', '%'.$request->get('name').'%');
            });
        }

        switch ($sort['sort'] ?? null) {
            default:
                $query->orderBy('created_at', 'DESC');
                break;
            case 'alpha':
                $query->orderBy('name');
                break;
            case 'alpha-reverse':
                $query->orderBy('name', 'DESC');
                break;
            case 'reputation':
                $query->orderBy('ranks.sort', 'DESC')->orderBy('name');
                break;
            case 'newest':
                $query->orderBy('created_at', 'DESC');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'ASC');
                break;
        }

        return view('guilds.index', [
            'guilds'    => $query->paginate(30)->appends($request->query()),
        ]);
    }

    /**
     * Shows an individual guild.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuild(Request $request, $id) {
        $guild = Guild::where('id', $id)->first();

        if (!$guild) {
            abort(404);
        }

        return view('guilds.guild', [
            'guild' => $guild,
        ]);
    }

    /**
     * Shows the edit page for an individual guild.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildEdit($id) {
    }

    /**
     * Shows the edit page for an individual guild.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postGuildEdit($id) {
    }

    /**
     * Shows the guild shop page should the shop be active.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildShop($id) {
        $guild = Guild::where('id', $id)->first();

        return view('guilds.shop', [
            'guild' => $guild,
            //TODO: add shop variables and data
            //TODO: make shop not viewable if the shop is not active
        ]);
    }

    /**
     * Shows the guild character index.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildCharacters($id) {
        $guild = Guild::where('id', $id)->first();

        return view('guilds.characters', [
            'guild' => $guild,
            //TODO Add guild character variables and data
        ]);
    }

    /**
     * Shows the guild member index, including member ranks.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildMembers($id) {
        $guild = Guild::where('id', $id)->first();

        return view('guilds.members', [
            'guild' => $guild,
            //TODO Get guild members and their ranks
        ]);
    }

    /**
     * Shows the guild inventory.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildInventory($id) {
        $guild = Guild::where('id', $id)->first();

        return view('guilds.inventory', [
            'guild' => $guild,
            //TODO get guild inventory
        ]);
    }

    /**
     * Shows the guild bank.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildBank($id) {
        $guild = Guild::where('id', $id)->first();

        return view('guilds.bank', [
            'guild' => $guild,
            //TODO get guild bank
        ]);
    }

    //Future TODO:
    /*
     * Guild armory
     * Guild events
     *
     */
}
