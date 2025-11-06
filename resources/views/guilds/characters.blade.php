@extends('layouts.app')

@section('title')
    {{ ucwords(__('guilds.guilds')) }}
@endsection

@section('sidebar')
    @include('guilds._sidebar')
@endsection

@section('content')
    {!! breadcrumbs([ucwords(__('guilds.guilds')) => __('guilds.guilds')]) !!}

    <h1>{{ $guild->name }}'s Characters</h1>
    <div class="row">
        <div class="col-md col-md-12">
            Content here
        </div>
    </div>
@endsection
