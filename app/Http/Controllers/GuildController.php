<?php

namespace App\Http\Controllers;

use App\Models\Guild\Guild;
use App\Models\Guild\GuildShop;
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
    public function getGuildIndex() {
        return view('guilds.index');
    }

    /**
     * Shows an individual guild.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuild(Request $request, $id) {
        $guild = Guild::active()->where('id', $id)->first();

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
     * Shows the guild shop page should the shop be active
     * 
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildShop($id) {
        $guild = Guild::active()->where('id', $id)->first();

        return view('guilds.shop', [
            'guild' => $guild,
            //TODO: add shop variables and data
            //TODO: make shop not viewable if the shop is not active
        ]);
    }

    /**
     * Shows the guild character index
     * 
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildCharacters($id) {
        $guild = Guild::active()->where('id', $id)->first();

        return view('guilds.characters', [
            'guild' => $guild,
            //TODO Add guild character variables and data
        ]);
    }

    /**
     * Shows the guild member index, including member ranks
     * 
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildMembers($id) {
        $guild = Guild::active()->where('id', $id)->first();

        return view('guilds.members', [
            'guild' => $guild,
            //TODO Get guild members and their ranks
        ]);
    }

    /**
     * Shows the guild inventory
     * 
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildInventory($id) {
        $guild = Guild::active()->where('id', $id)->first();

        return view('guilds.inventory', [
            'guild' => $guild,
            //TODO get guild inventory
            //Possible TODO: get guild bank on this page as well
        ]);
    }

    //Future TODO:
    /**
     * Guild armory
     * Guild events
     * 
     */
}
