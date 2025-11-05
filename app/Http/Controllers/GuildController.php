<?php

namespace App\Http\Controllers;

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
}
