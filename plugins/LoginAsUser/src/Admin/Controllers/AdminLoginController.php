<?php
namespace Plugins\LoginAsUser\src\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller {
    /**
     * Show the login selection screen.
     */
    public function getLoginAs() {

        if ( !Auth::user()->isStaff ) {
            abort(404);
        }

        $users = User::visible()->pluck('name', 'id')->toArray();
        return view('LoginAsUser::select_user', compact('users'));
    }

    /**
     * Execute the instant login event.
     */
    public function login(Request $request) {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        if (Auth::check()) {
            $request->session()->put('impersonator_id', Auth::id());
        }

        Auth::loginUsingId($request->user_id);
        $request->session()->regenerate();

        return redirect('/')->with('status', 'Logged in via plugin successfully!');
    }

    public function revert(Request $request) {
        if ($request->session()->has('impersonator_id')) {
            $originalId = $request->session()->get('impersonator_id');

            Auth::loginUsingId($originalId);

            $request->session()->forget('impersonator_id');
            $request->session()->regenerate();

            return redirect('/')->with('status', 'Succefully returned to original account.');
        }

        return redirect('/');
    }
}
