<?php

namespace App\Http\Controllers;

use Auth;
use Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\SurrenderManager;

use App\Models\Rarity;
use App\Models\Feature\FeatureCategory;
use App\Models\Marking\Marking;
use App\Models\Character\CharacterMarking;
use App\Models\Adoption\Adoption;
use App\Models\Adoption\Surrender;
use App\Models\Adoption\AdoptionStock;
use App\Models\Adoption\AdoptionLog;
use App\Models\Adoption\AdoptionCurrency;
use App\Models\Character\Character;
use App\Models\Character\CharacterCategory;
use App\Models\Currency\Currency;

class SurrenderController extends Controller
{
    /**
     * Shows the user's surrender log.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request)
    {
        $surrenders = Surrender::where('user_id', Auth::user()->id);
        $type = $request->get('type');
        if(!$type) $type = 'Pending';

        $surrenders = $surrenders->where('status', ucfirst($type));

        return view('home.surrenders', [
            'surrenders' => $surrenders->orderBy('id', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    //
    /**
     * Shows surrender form
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSurrender()
    {
        $characters = Character::orderBy('id')->where('user_id', Auth::user()->id)->where(function ($query) {
            return $query->where('is_sellable', 1)->orWhere('is_tradeable', 1)->orwhere('is_giftable', 1);
        })->get()->pluck('fullName', 'id');
        $adoption = Adoption::where('id', 1)->where('is_active', 1)->first();
        if(!$adoption) abort(404);
        return view('adoptions.surrender_form', [
            'adoption' => $adoption,
            'characters' =>  $characters,
            'adoptions' => Adoption::where('is_active', 1)->get(),
            'currencies' => Currency::orderBy('name')->pluck('name', 'id'),
            'primaryCurrency' => Settings::get('background_location_change_currency'),
        ]);
    }
    /**
     * posts surrender form
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postSurrender(Request $request, SurrenderManager $service) {

        $request->validate(Surrender::$createRules);
        $data = $request->only(['character_id', 'notes', 'worth', 'currency_id']);

        if($service->createSurrender($data, Auth::user())) {
            flash('Surrender submitted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('surrenders');
    }

    /**
     * Shows the surrender page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPublicSurrender($id)
    {
        $surrender = Surrender::viewable(Auth::user())->where('id', $id)->first();
        if(!$surrender) abort(404);
        $features = $surrender->character->image->features()->get();

        $cost_result = json_decode($this->getCharacterValue($surrender->character->id)->getContent(), true);
        return view('home.surrender', [
            'estimate' => $cost_result,
            'surrender' => $surrender,
            'user' => $surrender->user,
            'worth' => Currency::find($surrender->currency_id),
        ]);
    }

    /**
     * Gets the estimated worth of a character.
     */
    public function getCharacterValue($id) {
        $result = Surrender::getCharacterValue($id);

        return $result;
    }

}
