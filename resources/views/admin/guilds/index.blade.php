@extends('admin.layout')

@section('admin-title')
    Guilds
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Guilds' => 'admin/guilds']) !!}

    <h1>Guilds</h1>

    <p>Here you can find all guilds created by players
    @endsection
