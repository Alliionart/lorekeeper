<?php

namespace App\Http\Controllers;

use App\Models\IndexSiteData;
use Illuminate\Http\Request;

class IndexController extends Controller {
    
    public function siteSearch(Request $request) {

        $input = $request->input('i');

        $result = IndexSiteData::where('name', 'like', '%' . $input . '%')
            ->orWhere('api', 'like', '%' . $input . '%')
            ->get();

        $result = DB::table('index_site_data')
        ->where('name', 'like', '%' . $input . '%')
        ->orWhere('api', 'like', '%' . $input . '%')
        ->get();

        foreach ($result as $r) {
            $url = $r->findUrlStructure();
        }

        return view('search', ['result' => $result])->render();

    }
}