<?php

namespace App\Http\Controllers;

use App\Models\Carrier\Carrier;
use App\Models\Carrier\MarkingCarrier;
use App\Models\Marking\Marking;
use App\Models\Rarity;
use App\Models\SitePage;
use Illuminate\Http\Request;

class CarrierController extends Controller {
    public function getCarriersPageView() {
        return view('designhub.carriers');
    }

    public function getCarrierPage(Request $request) {
        return view('designhub.carriers', [
            'carrier_info'  => SitePage::where('key', 'carrier-start')->first(),
            'carriers'      => $this->getCarriersByMarkingRarity(),
            'rarities'      => Rarity::whereIn('id', Marking::select('rarity_id')->distinct()->get())->get(),
        ]);
    }

    public function getCarriersByMarkingRarity() {
        $rarities = Rarity::whereIn('id', Marking::select('rarity_id')->distinct()->get())->get();
        $carriers = Carrier::get();

        $carriers_by_rarity = [];

        foreach($carriers as $carrier) {
            $markings = MarkingCarrier::where('carrier_id', $carrier->id)->pluck('marking_id')->toArray();
            $marking = [];
            $rarities = [];
            foreach($markings as $marking_id) {
                $rarities[] = Marking::where('id', $marking_id)->pluck('rarity_id')->toArray();
                //Get the marking attributes
                $marking_temp = Marking::where('id', $marking_id)->first();
                if($marking_temp) {
                    $marking[] = '<a href="' . $marking_temp->getUrlAttribute() . '">' . $marking_temp->name . '</a>';
                }
            }
            if(count($rarities) > 1) {
                $rarity = 'Special';
            } else {
                $rarity = $rarities[0][0];
            }
            if(!isset($carriers_by_rarity[$rarity])) {
                $carriers_by_rarity[$rarity] = [];
            }

            $carriers_by_rarity[$rarity][] = [
                'name' => $carrier->name,
                'id'   => $carrier->id,
                'description'   => $carrier->description,
                'rarity'    => $rarity,
                'markings'   => $marking,
            ];
        }

        return $carriers_by_rarity;
    }
}
