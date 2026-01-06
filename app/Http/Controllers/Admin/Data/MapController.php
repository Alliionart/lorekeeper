<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Map;
use App\Services\MapManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MapController extends Controller {
    /**
     * Shows the page index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.map.index', [
            'map_items' => Map::orderBy('name')->where('map_id', null)->where('type', 'map')->paginate(20),
        ]);
    }

    /**
     * Shows the create page page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateMapItem() {
        return view('admin.map.create_edit_map_item', [
            'map_item' => new Map,
        ]);
    }

    /**
     * Shows the edit page page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditMapItem($id) {
        $map_item = Map::find($id);
        if (!$map_item) {
            abort(404);
        }

        return view('admin.map.create_edit_map_item', [
            'map_item' => $map_item,
        ]);
    }

    /**
     * Shows the edit map settings page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMapSettings() {
        return view('admin.map.create_edit_map', [
            'map_settings'  => null,
        ]);
    }

    /**
     * Creates or edits a map.
     *
     * @param App\Services\MapManager $service
     * @param int|null                $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditMap(Request $request, MapManager $service, $id = null) {
        $id ? $request->validate(Map::$updateRules) : $request->validate(Map::$createRules);
        $data = $request->only([
            'name', 'description', 'image',
        ]);

        if ($id && $service->updateMap(Map::find($id), $data, Auth::user())) {
            flash('Map item updated successfully.')->success();
        } elseif (!$id && $map = $service->createMap($data, Auth::user())) {
            flash('Map item created successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Creates or edits a page.
     *
     * @param App\Services\MapManager $service
     * @param int|null                $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditMapItem(Request $request, MapManager $service, $id = null) {
        $id ? $request->validate(Map::$updateRules) : $request->validate(Map::$createRules);
        $data = $request->only([
            'name', 'type', 'description', 'latitude', 'longitude', 'icon', 'url',
        ]);
        if ($id && $service->updateMapItem(Map::find($id), $data, Auth::user())) {
            flash('Map item updated successfully.')->success();
        } elseif (!$id && $map_item = $service->createMapItem($data, Auth::user())) {
            flash('Map item created successfully.')->success();

            return redirect()->to('admin/map/edit/'.$map_item->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the page deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteMapItem($id) {
        $map_item = Map::find($id);

        return view('admin.map._delete_map_item', [
            'map_item' => $map_item,
        ]);
    }

    /**
     * Deletes a page.
     *
     * @param App\Services\MapManager $service
     * @param int                     $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteMapItem(Request $request, MapManager $service, $id) {
        if ($id && $service->deleteMapItem(Map::find($id))) {
            flash('Map item deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/pages');
    }
}
