@extends('layouts.app')

@section('title')
    {{ ucwords(__('guilds.guilds')) }}
@endsection

@section('sidebar')
    @include('guilds._sidebar')
@endsection

@section('content')

    {!! breadcrumbs([ucwords(__('guilds.guilds')) => __('guilds.guilds'), $guild->name => $guild->name]) !!}

    <div class="jumbotron jumbotron-fluid text-center rounded bg-dark mb-5">
        <h1 class="display-5 text-white">{{ $guild->name }}</h1>
    </div>

    <!-- Details -->
    <div class="row">
        <div class="col-md col-md-8">
            <h3>About {{ $guild->name }}</h3>
            {!! $guild->parsed_description !!}
        </div>

        <div class="col-md col-md-4">
            <div class="card">
                <h4 class="card-header">Details</h4>
                <div class="card-body">
                    @if($guild->status  === 'active')
                        <span class="h6 p-1 rounded bg-success text-white">Active</span>
                    @else
                        <span class="h6 p-1 rounded bg-secondary text-white">Inactive</span>
                    @endif
                        <span class="h6 p-1 rounded border ml-2 {{ $guild->open_new_users ? 'border-success text-success' : 'border-danger text-danger' }}">{{ $guild->open_new_users ? 'Open Applications' : 'Closed Applications' }}</span>
                </div>
            </div>
        </div>
    </div>

    <hr> 

    <!--basic info -->
    <div class="row">
        <div class="col-md col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Members</h3>
                    <a href="{{ $guild->getViewUrlAttribute() . '/members' }}" class="btn btn-outline-primary">View All Members</a>
                </div>
                <div class="card-body">
                    @if($guild->members)
                        @include('guilds._member_table', ['members' => $guild->members, 'limit' => 5])
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Characters</h3>
                    <a href="{{ $guild->getViewUrlAttribute() . '/characters' }}" class="btn btn-outline-primary">View All Characters</a>
                </div>
                <div class="card-body">
                    @if($guild->characters)
                        @include('guilds._characters', ['characters' => $guild->characters, 'limit' => 5])
                    @endif
                </div>
            </div>
        </div>
    </div>

       

@endsection
