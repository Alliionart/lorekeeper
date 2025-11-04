@extends('user.layout', ['user' => isset($user) ? $user : null])

@section('profile-title')
    {{ $user->name }}'s Profile
@endsection

@section('meta-img')
    {{ $user->avatarUrl }}
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url]) !!}

    @if (mb_strtolower($user->name) != mb_strtolower($name))
        <div class="alert alert-info">This user has changed their name to <strong>{{ $user->name }}</strong>.</div>
    @endif
    
    @if(Settings::get('event_teams') && $user->settings->team)
        <div class="col-md-2 text-center">
            <a href="{{ url('event-tracking') }}">
                @if($user->settings->team->has_image)
                    <img src="{{ $user->settings->team->imageUrl }}" class="mw-100"/>
                @endif
                <h5>{{ $user->settings->team->name }}</h5>
            </a>
        </div>
    @endif

    @if ($user->is_banned)
        <div class="alert alert-danger">This user has been banned.</div>
    @endif

    @if ($user->is_deactivated)
        <div class="alert alert-info text-center">
            <h1>
                <div style=" float:left;">
                    {!! $user->userBorder(150) !!}
                </div>
                {!! $user->displayName !!}
            </h1>
            <p>This account is currently deactivated, be it by staff or the user's own action. All information herein is hidden until the account is reactivated.</p>
            @if (Auth::check() && Auth::user()->isStaff)
                <p class="mb-0">As you are staff, you can see the profile contents below and the sidebar contents.</p>
            @endif
        </div>
    @endif

    @if (!$user->is_deactivated || (Auth::check() && Auth::user()->isStaff))
        @include('user._profile_content', ['user' => $user, 'deactivated' => $user->is_deactivated])
    @endif

@endsection
