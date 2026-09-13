@if (session()->has('impersonator_id'))
    <div class="bg-dark text-white p-2 d-flex justify-content-between align-items-center w-100" style="z-index: 9999; position: fixed; top:0; left:0;">
        <div>
            <strong>Logged In As | </strong> You are currently logged in as
            <span style="text-decoration: underline;">{{ Auth::user()->name }}</span> ({{ Auth::user()->email }}).
        </div>
        <form action="/login-as/revert" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="btn btn-outline-light">
                Return to Account
            </button>
        </form>
    </div>
@endif
