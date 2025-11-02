<?php namespace App\Http\Controllers\Admin;

use Auth;
use DB;
use Exception;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Cultivation\CultivationArea;
use App\Models\Cultivation\CultivationPlot;
use App\Models\Item\Item;
use App\Models\Item\ItemTag;

use App\Http\Controllers\Controller;
use App\Services\CultivationService;

class CultivationController extends Controller
{
    /**
     * Shows the area index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getAreaIndex(Request $request)
    {
        return view('admin.cultivation.area_index', [
            'areas' => CultivationArea::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the plot index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPlotIndex(Request $request)
    {
        return view('admin.cultivation.plot_index', [
            'plots' => CultivationPlot::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create/edit area page.
     *
     * @param  int|null  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateEditArea($id = null)
    {
        $area = null;
        if ($id) {
            $area = CultivationArea::find($id);
            if (!$area) abort(404);
        }
        else $area = new CultivationArea;
        return view('admin.cultivation.create_edit_area', [
            'area' => $area,
            'plots' => CultivationPlot::all()->pluck('name', 'id')
        ]);
    }

    /**
     * Shows the create/edit plot page.
     *
     * @param  int|null  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateEditPlot($id = null)
    {
        $plot = null;
        if ($id) {
            $plot = CultivationPlot::find($id);
            if (!$plot) abort(404);
        }
        else $plot = new CultivationPlot;

        return view('admin.cultivation.create_edit_plot', [
            'plot' => $plot,
            'items' => ItemTag::with('item')->where('tag', 'seed')->get()->pluck('item.name', 'item.id')
        ]);
    }

    /**
     * Creates or edits an area.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  App\Services\RaffleService  $service
     * @param  int|null                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditArea(Request $request, CultivationService $service, $id = null)
    {
        $data = $request->only(['name', 'description', 'parsed_description', 'background_image', 'remove_background', 'plot_image', 'remove_plot', 'max_plots', 'is_active', 'plot_id']);
        $area = null;
        if (!$id) $area = $service->createArea($data);
        else if ($id) $area = $service->updateArea(CultivationArea::find($id), $data);
        if ($area) {
            flash('Area ' . ($id ? 'updated' : 'created') . ' successfully!')->success();
            return redirect()->to("/admin/cultivation/areas/edit/".$area->id);
        }
        else {
            flash('Couldn\'t create area.')->error();
            return redirect()->back()->withInput();  
        }
    }

    
    /**
     * Creates or edits a plot.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  App\Services\RaffleService  $service
     * @param  int|null                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditPlot(Request $request, CultivationService $service, $id = null)
    {
        $data = $request->only(['name', 'description', 'parsed_description', 'is_active','item_id',
        'stage_1_image', 'stage_2_image', 'stage_3_image', 'stage_4_image', 'stage_5_image', 
        'remove_stage_1', 'remove_stage_2', 'remove_stage_3', 'remove_stage_4', 'remove_stage_5', 

        ]);
        $plot = null;
        if (!$id) $plot = $service->createPlot($data);
        else if ($id) $plot = $service->updatePlot(CultivationPlot::find($id), $data);
        if ($plot) {
            flash('Plot ' . ($id ? 'updated' : 'created') . ' successfully!')->success();
            return redirect()->to("/admin/cultivation/plots/edit/".$plot->id);
        }
        else {
            flash('Couldn\'t create plot.')->error();
            return redirect()->back()->withInput();  
        }
    }
    
    public function postSortAreas(Request $request, CultivationService $service)
    {
        if($service->sortAreas($request->get('sort'), Auth::user())) {
            flash('Areas sorted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    public function postSortPlot(Request $request, CultivationService $service)
    {
        if($service->sortPlot($request->get('sort'), Auth::user())) {
            flash('Plots sorted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Get the area deletion modal.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteArea($id)
    {
        $area = CultivationArea::find($id);
        return view('admin.cultivation._delete_area', [
            'area' => $area,
        ]);
    }

    /**
     * Get the plot deletion modal.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeletePlot($id)
    {
        $plot = CultivationPlot::find($id);
        return view('admin.cultivation._delete_plot', [
            'plot' => $plot,
        ]);
    }

    public function postDeleteArea(Request $request, CultivationService $service, $id)
    {
        if($id && $service->deleteArea(CultivationArea::find($id), Auth::user())) {
            flash('Area deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/cultivation/areas');
    }

    public function postDeletePlot(Request $request, CultivationService $service, $id)
    {
        if($id && $service->deletePlot(CultivationPlot::find($id), Auth::user())) {
            flash('Plot deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/cultivation/plots');
    }
    
}
