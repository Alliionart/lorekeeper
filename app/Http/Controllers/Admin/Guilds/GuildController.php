<?php

namespace App\Http\Controllers\Admin\Guilds;

use App\Http\Controllers\Controller;
use App\Models\Guild\Guild;
use App\Models\Guild\GuildUpdate;
use App\Models\User\User;
use App\Services\GuildManager;
use Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Settings;

class GuildController extends Controller {
    use SoftDeletes;
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

        return view('admin.guilds.index', [
            'guilds'    => $query->paginate(30)->appends($request->query()),
        ]);
    }

    /**
     * Shows the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildQueue(Request $request, $action = 'pending') {
        $query = GuildUpdate::where('status', $action);
        $data = $request->only(['sort', 'type']);

        switch ($data['sort'] ?? null) {
            default:
            case 'newest':
                $query->orderBy('created_at', 'DESC');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'ASC');
                break;
        }

        switch ($data['type'] ?? null) {
            default:
            case 'all':
                break;
            case 'new':
                $query->where('is_update', 0);
                break;
            case 'update':
                $query->where('is_update', 1);
                break;
        }

        return view('admin.guilds.queue', [
            'requests'    => $query->paginate(30)->appends($request->query()),
        ]);
    }

    /**
     * Edit page an individual guild.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildRequest(Request $request, $id) {
        $request = GuildUpdate::where('id', $id)->first();

        if (!$request) {
            abort(404);
        }

        return view('admin.guilds.request', [
            'request' => $request,
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
        $guild = Guild::where('id', $id)->first();

        if (!$guild) {
            abort(404);
        }

        return view('admin.guilds.guild', [
            'guild'       => $guild,
            'userOptions' => User::visible()->orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * create page an individual guild.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateGuild() {
        return view('admin.guilds.guild', [
            'guild'       => new Guild(),
            'userOptions' => User::visible()->orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Edit page an individual guild.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postCreateEditGuild(Request $request, GuildManager $service, $id = null) {
        $id ? $request->validate(Guild::$updateRules) : $request->validate(Guild::$createRules);
        $data = $request->only([
            'name', 'description', 'location',
            'location', 'max_users', 'max_characters',
            'open_new_users', 'status',
            'logo', 'remove_logo', 'owner_id',
        ]);

        if ($id && $service->updateGuild(Guild::find($id), $data, Auth::user())) {
            flash('Guild updated successfully.')->success();
        } elseif (!$id && $guild = $service->createGuild($data, Auth::user())) {
            flash('Guild created successfully.')->success();

            return redirect()->to('admin/guilds/edit/'.$guild->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Pushes the guild request to the service for approval or rejection.
     *
     * @param mixed $id
     * @param mixed $action
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postGuildRequest(Request $request, GuildManager $service, $id = null, $action = null) {
        $data = $request->only([
            'staff_comment', 'remove_logo',
        ]);

        $request = GuildUpdate::where('id', $id)->first();

        if (!$request || !in_array($action, ['approve', 'reject'])) {
            abort(404);
        }

        if ($action == 'approve' && $service->approveRequest($request, $data, Auth::user())) {
            flash('Guild approved successfully.')->success();
        } elseif ($action == 'reject' && $service->rejectRequest($request, $data, Auth::user())) {
            flash('Guild rejected successfully.')->success();

            return redirect()->to('admin/guilds/queue/'.$request->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the delete MYO slot modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGuildDelete($id) {
        $this->guild = Guild::where('id', $id)->first();
        if (!$this->guild) {
            abort(404);
        }

        return view('admin.guilds._delete_guild_modal', [
            'guild' => $this->guild,
        ]);
    }

    /**
     * Deletes a character.
     *
     * @param App\Services\CharacterManager $service
     * @param mixed                         $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postGuildDelete(Request $request, GuildManager $service, $id) {
        $this->guild = Guild::where('id', $id)->first();
        if (!$this->guild) {
            abort(404);
        }

        if ($service->deleteGuild($this->guild, Auth::user())) {
            flash('Guild deleted successfully.')->success();

            return redirect()->to('masterlist');
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
