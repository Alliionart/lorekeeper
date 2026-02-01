<?php

namespace App\Http\Controllers\Admin\Data;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Award\Award;
use App\Models\Background\Background;
use App\Models\Background\BackgroundCondition;
use App\Models\WorldExpansion\Location;
use App\Models\Species\Species;
use App\Models\Item\Item;
use App\Models\User\User;
use App\Services\BackgroundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class BackgroundController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Background Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of character backgrounds.
    |
    */

    /**
     * Shows the background index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBackgroundsIndex(Request $request) {
        $query = Background::query();
        $data = $request->all();

        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        if (isset($data['location'])) {
            $query->whereHas('conditions', function ($q) use ($data) {
                $q->where('location', $data['location']);
            });
        }

        if (isset($data['species'])) {
            $query->whereRaw("JSON_EXTRACT(image_data, '$.\"{$data['species']}\"') IS NOT NULL");
        }

        if (isset($data['conditions'])) {
            if($data['conditions'] == 'null') {
                $data['conditions']  = null;
            }
            $query->whereHas('conditions', function ($q) use ($data) {
                $q->where('type', $data['conditions']);
            });
        }

        return view('admin.backgrounds.backgrounds', [
            'backgrounds'   => $query->paginate(20)->appends($request->query()),
            'locations'     => ['' => 'All Locations'] + Location::all()->where('has_backgrounds', 1)->pluck('name', 'id')->toArray(),
            'conditions'    => ['' => 'All Conditions'] + BackgroundCondition::all()->pluck('type', 'type')->toArray() + [ 'null' => 'Free to Use'],
            'species'       => ['' => 'All Species'] + Species::orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the create background page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateBackground() {
        $levels = DB::table('site_settings')->where('key', 'xp_levels')->pluck('value');
        $levels = isset($levels[0]) ? (array) json_decode($levels[0]) : null;
        foreach($levels as $level => $exp) {
            $levels[$level] = $level;
        }

        return view('admin.backgrounds.create_edit_background', [
            'background'    => new Background,
            'image_data'    => null,
            'users'         => User::query()->orderBy('name')->pluck('name', 'id')->toArray(),
            'locations'     => ['' => 'None'] + Location::all()->where('has_backgrounds', 1)->pluck('name', 'id')->toArray(),
            'items'         => ['' => 'None'] + Item::orderBy('name')->pluck('name', 'id')->toArray(),
            'awards'        => ['' => 'None'] + Award::orderBy('name')->pluck('name', 'id')->toArray(),
            'specieses'     => ['' => 'None'] + Species::orderBy('name')->pluck('name', 'id')->toArray(),
            'statuses'      => ['' => 'None'] + $levels,
        ]);
    }

    /**
     * Shows the edit background page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditBackground($id) {
        $background = Background::find($id);
        if (!$background) {
            abort(404);
        }
        $levels = DB::table('site_settings')->where('key', 'xp_levels')->pluck('value');
        $levels = isset($levels[0]) ? (array) json_decode($levels[0]) : null;
        foreach($levels as $level => $exp) {
            $levels[$level] = $level;
        }

        return view('admin.backgrounds.create_edit_background', [
            'background'    => $background,
            'image_data'    => $background->image_data ? json_decode($background->image_data, true) : null,
            'users'         => User::query()->orderBy('name')->pluck('name', 'id')->toArray(),
            'locations'     => ['' => 'None'] + Location::all()->where('has_backgrounds', 1)->pluck('name', 'id')->toArray(),
            'items'         => ['' => 'None'] + Item::orderBy('name')->pluck('name', 'id')->toArray(),
            'awards'        => ['' => 'None'] + Award::orderBy('name')->pluck('name', 'id')->toArray(),
            'specieses'     => ['' => 'None'] + Species::orderBy('name')->pluck('name', 'id')->toArray(),
            'statuses'      => ['' => 'None'] + $levels,
        ]);
    }

    /**
     * Creates or edits a background.
     *
     * @param App\Services\BackgroundService $service
     * @param int|null                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditBackground(Request $request, BackgroundService $service, $id = null) {
        $id ? $request->validate(Background::$updateRules) : $request->validate(Background::$createRules);
        $data = $request->only([
            'name', 'image', 'is_visible', 'use_cropper',
            'user_id', 'guild_id', 'award_id', 'location', 'status', 'item_id',
        ]);

        if ($id && $service->updateBackground(Background::find($id), $data, Auth::user())) {
            flash('Background updated successfully.')->success();
        } elseif (!$id && $background = $service->createBackground($data, Auth::user())) {
            flash('Background created successfully.')->success();

            return redirect()->to('admin/data/background/edit/'.$background->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the background deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteBackground($id) {
        $background = Background::find($id);

        return view('admin.backgrounds._delete_background', [
            'background' => $background,
        ]);
    }

    /**
     * Deletes a background.
     *
     * @param App\Services\BackgroundService $service
     * @param int                            $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteBackground(Request $request, BackgroundService $service, $id) {
        if ($id && $service->deleteBackground(Background::find($id), Auth::user())) {
            flash('Background deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/backgrounds');
    }
}
