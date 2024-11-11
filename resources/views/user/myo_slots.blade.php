@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s Genotupes
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Genotypes' => $user->url . '/myos']) !!}

    <h1>
        {!! $user->displayName !!}'s Genotypes
    </h1>

    @include('user._characters', ['characters' => $myos, 'myo' => true])
@endsection
