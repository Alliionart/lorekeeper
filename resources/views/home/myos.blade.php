@extends('home.layout')

@section('home-title')
    My Genotypes
@endsection

@section('home-content')
    {!! breadcrumbs(['Characters' => 'characters', 'My Genotypes' => 'myos']) !!}

    <h1>
        My Genotypes
    </h1>

    <p>This is a list of Genotypes you own - click on a slot to view details about it. Genotypes can be submitted for design approval from their respective pages.</p>
    <div class="row">
        @foreach ($slots as $slot)
            @include('browse._genotype_box', ['character' => $slot])
        @endforeach
    </div>
@endsection
