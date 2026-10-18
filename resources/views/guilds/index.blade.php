@extends('layouts.app')

@section('title')
    {{ ucwords(__('guilds.guilds')) }}
@endsection

@section('content')
    {!! breadcrumbs([ucwords(__('guilds.guilds')) => __('guilds.guilds')]) !!}

    <h1>{{ ucwords(__('guilds.guilds')) }}</h1>

    @if (Auth::check() && Auth::user())
        <div>
            <p>This is a list of all {{ ucwords(__('guilds.guilds')) }} on the site, including disbanded {{ __('guilds.guilds') }}. If you want to form a {{ ucwords(__('guilds.guild')) }}, you can form a new {{ __('guilds.guild') }}.</p>
            <a href="{{ url(__('guilds.guilds') . '/new') }}" class="btn btn-success float-right ml-2">Form a New {{ __('guilds.guild') }}</a>
        </div>
    @else
        <p>This is a list of all {{ ucwords(__('guilds.guilds')) }} on the site, including disbanded {{ __('guilds.guilds') }}. You must be logged in to form a new {{ __('guilds.guild') }}.</p>
    @endif


    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select(
                'sort',
                [
                    'alpha' => 'Sort Alphabetically (A-Z)',
                    'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                    'reputation' => 'Sort by Reputation',
                    'newest' => 'Newest First (Default)',
                    'oldest' => 'Oldest First',
                ],
                Request::get('sort') ?: 'newest',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="row">
        {!! $guilds->render() !!}
        @foreach ($guilds->chunk(1) as $chunk)
            @foreach ($chunk as $guild)
                <div class="col-md-12 my-2">
                    @include('guilds._guild_box', ['guild' => $guild])
                </div>
            @endforeach
        @endforeach
        {!! $guilds->render() !!}
    </div>

    <div class="text-center mt-4 small text-muted">{{ $guilds->total() }} result{{ $guilds->total() == 1 ? '' : 's' }} found.</div>
@endsection
@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.applyButton').on('click', function(e) {
                e.preventDefault();
                var guildId = $(this).data('guild-id');
                var targetModal = $(this).data('target');

                loadModal("{{ url(__('guilds.guilds') . '/') }}/" + guildId + "/apply", 'Submit Application');
            });
        });
    </script>
@endsection
