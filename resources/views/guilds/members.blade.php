@extends('layouts.app')

@section('title')
    {{ ucwords(__('guilds.guilds')) }}
@endsection

@section('content')
    {!! breadcrumbs([ucwords(__('guilds.guilds')) => __('guilds.guilds')]) !!}

    <h1>{{ $guild->name }}'s Members</h1>
    <div class="row">
        <div class="col-md col-md-12">
            Content here
        </div>
    </div>
@endsection
