@extends('home.layout')

@section('home-title')
    Your Breedings
@endsection

@section('home-content')
    {!! breadcrumbs(['Breedings' => 'breedings']) !!}

    <h1>Breedings</h1>

    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'sort',
                    [
                        'newest' => 'Newest First',
                        'oldest' => 'Oldest First',
                    ],
                    Request::get('sort') ?: 'oldest',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('month', $months, Request::get('month') ?: '', ['class' => 'form-control']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
    {!! Form::close() !!}

    @if (count($breedings))
        {!! $breedings->render() !!}
        <div class="accordion">
            @foreach($breedingsGrouped as $monthYear => $group)
                <div class="card">
                    <div class="card-header" id="heading-{{ $monthYear }}">
                    <h3 class="mb-0">
                        <button class="h3 mb-0 btn btn-link" type="button" data-toggle="collapse" data-target="#collapse{{ $monthYear }}">
                            {{ \Carbon\Carbon::createFromFormat('m-Y', $monthYear)->format('F Y') }}
                        </button>
                    </h3>
                    </div>
                    <div id="collapse{{ $monthYear }}" class="show" aria-labelledby="heading{{ $monthYear }}">
                    <div class="card-body">
                        @foreach($group as $breeding)
                            @include('breeding._summary', ['breeding' => $breeding])
                        @endforeach
                    </div>
                    </div>
                </div>
            @endforeach
        </div>
        {!! $breedings->render() !!}
        <div class="text-center mt-4 small text-muted">{{ $breedings->total() }} result{{ $breedings->total() == 1 ? '' : 's' }} found.</div>
    @else
        <p>No breeedings found.</p>
    @endif

@endsection

@section('scripts')
    @parent
@endsection
