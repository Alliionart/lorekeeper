<?php

namespace App\Http\Controllers\Admin\Guilds;

use App\Http\Controllers\Controller;
use App\Models\Guild\Guild;
use Illuminate\Http\Request;
use Settings;

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
        return view('admin.guilds.index');
    }

    /**
     * Shows the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildQueue(Request $request) {
        //$query = GuildRequest::query();
        $data = $request->only(['sort', 'type']);
        $gq_catgory = Settings::get('guild_queue_request_category') ?? null;

        switch ($data['sort'] ?? null) {
            default:
            case 'newest':
                //$query->orderBy('created_at', 'DESC');
                break;
            case 'oldest':
                //$query->orderBy('created_at', 'ASC');
                break;
        }

        switch ($data['type'] ?? null) {
            default:
            case 'all':
                break;
            case 'creation':
                //$query->where('queue_category_id', $gq_catgory)->where('queue_type', 'new_guild');
                break;
            case 'update':
                //$query->where('queue_category_id', $gq_catgory)->where('queue_type', 'update_guild');
                break;
        }

        return view('admin.guilds.queue', [
            //'requests'    => $query->paginate(30)->appends($request->query()),
        ]);
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

        if (!$guild) {
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
