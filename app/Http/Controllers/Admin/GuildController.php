<?php
namespace App\Http\Controllers\Admin;

use App\Facades\Settings;
use App\Models\Queue\Queue;
use App\Models\Queue\QueueCategory;
use App\Models\Guilds\Guilds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuildController extends Controller
{
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
        return view('admin.guilds.index');
    }

    /**
     * Edit page an individual guild.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditGuild(Request $request, $id) {
        $guild = Guild::active()->where('id', $id)->first();

        if (! $guild) {
            abort(404);
        }

        return view('admin.guilds.guild', [
            'guild' => $guild,
        ]);
    }

    /**
     * Edit page an individual guild.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postEditGuild(Request $request, $id) {
        
    }

}
