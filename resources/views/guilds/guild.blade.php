@extends('layouts.app')

@section('title')
    {{ ucwords(__('guilds.guilds')) }}
@endsection

@section('content')
    {!! breadcrumbs([ucwords(__('guilds.guilds')) => __('guilds.guilds'), $guild->name => $guild->name]) !!}

    <h1>{{ $guild->name }}</h1>
    <div class="row">
        <div class="col-md col-md-12">
            Content here
        </div>
    </div>

    <!--basic info -->
    <div class="row">
        <div class="col-md col-md-6">
            Member list -- Rank sorted
            <br><br>
            view more (leads to new page)
        </div>

        <div class="col-md col-md-6">
            Characters
            <br><br>
            view more (leads to new page)
        </div>
    </div>

    <hr>

    <!--details -->
    <div class="row">
        <div class="col-md col-md-4">
            About
            <br><br>
            Full Descript here
        </div>

        <div class="col-md col-md-4">
            Details
            <br><br>
            Things like Open to new members, auto accept new members (y/n) etc.
        </div>

        <div class="col-md col-md-4">
            Inventory
            <br><br>
            Pets
            <br><br>
            Guild Armory
            <br><br>
            Guild Bank
        </div>
    </div>
@endsection
