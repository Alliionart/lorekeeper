<?php

namespace App\Http\Controllers\Admin\Guilds;

use App\Http\Controllers\Controller;
use App\Models\Currency\Currency;
use App\Services\CurrencyManager;
use App\Services\InventoryManager;
use App\Models\Guild\Guild;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrantController extends Controller {
    /**
     * Grants or removes currency from a character.
     *
     * @param string                       $slug
     * @param App\Services\CurrencyManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postGuildCurrency($id, Request $request, CurrencyManager $service) {
        $data = $request->only(['currency_id', 'quantity', 'data']);
        if ($service->grantGuildCurrencies($data, Guild::where('id', $id)->first(), Auth::user())) {
            flash('Currency granted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Grants items to characters.
     *
     * @param string                        $slug
     * @param App\Services\InventoryManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postGuildItems($id, Request $request, InventoryManager $service) {
        $data = $request->only(['item_ids', 'quantities', 'data', 'disallow_transfer', 'notes']);
        if ($service->grantGuildItems($data, Guild::where('id', $id)->first(), Auth::user())) {
            flash('Items granted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
